<?php

require_once( sfConfig::get('sf_plugins_dir') . DIRECTORY_SEPARATOR . 'sfSAMLPlugin' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'sfSAMLAuth' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'BasesfSAMLAuthActions.class.php' );

class BuildspaceSAMLAuthActions extends BasesfSAMLAuthActions
{
    protected function signinProcess(sfWebRequest $request, $allowLicenseAccess=false)
    {
        Utilities::checkPostgresqlMultiplyAggregateFunction();

        $user = $this->getUser();

        // Create SimpleSAML module
        $simpleSAMLAuth = new SimpleSAML_Auth_Simple(sfConfig::get('app_saml_sp_name', 'default-sp'));

        $attributes = $simpleSAMLAuth->getAttributes();

        // If the user is authenticated from the IdP
        if ( $simpleSAMLAuth->isAuthenticated() && $eProjectUser = EProjectUserTable::getInstance()->findOneBy('id', $attributes['uid'][0]) )
        {
            // Try to find the user with his uid
            $guardUser = Doctrine_Core::getTable('sfGuardUser')->findOneBy('username', $eProjectUser->email);

            if ( !$guardUser )
            {
                $con = sfGuardUserTable::getInstance()->getConnection();

                try
                {
                    $con->beginTransaction();

                    // the user doesn't exist, we create a new one with random password
                    $guardUser = new sfGuardUser();
                    $guardUser->setUsername($eProjectUser->email);
                    $guardUser->setEmailAddress($eProjectUser->email);
                    $guardUser->setPassword(md5(microtime() . $eProjectUser->email . mt_rand()));
                    $guardUser->setIsActive(true);

                    $guardUser->setIsSuperAdmin((boolean) $eProjectUser->is_super_admin);
                    $guardUser->save($con);

                    $guardUser->refresh();

                    // will update profile as well
                    $userProfile                   = $guardUser->Profile;
                    $userProfile->eproject_user_id = $eProjectUser->id;
                    $userProfile->name             = $eProjectUser->name;
                    $userProfile->contact_num      = $eProjectUser->contact_number;

                    $userProfile->save($con);

                    $con->commit();
                }
                catch (Exception $e)
                {
                    $con->rollback();

                    throw $e;
                }
            }

            // Let the User signin
            // The auth is not remembered : the IdP can decide that
            $user->signOut();
            $user->signIn($guardUser, $remember = false);

            $isLicenseValid = Utilities::checkLicenseValidity();

            if(!$isLicenseValid)
            {
                if($user->getGuardUser()->is_super_admin && $allowLicenseAccess)
                {
                    $site = preg_replace('{/$}', '', sfConfig::get('app_e_project_url'));
                    return $this->redirect($site.'/license');
                }

                return $this->redirect('@no_access');
            }
        }

        // the user is not authenticated in symfony and from the IdP
        if ( $request->isXmlHttpRequest() && !$simpleSAMLAuth->isAuthenticated() && !$user->isAuthenticated())
        {
            $this->getResponse()->setHeaderOnly(true);
            $this->getResponse()->setStatusCode(401);

            return sfView::NONE;
        }

        // if we have been forwarded, then the referer is the current URL
        // if not, this is the referer of the current request
        $user->setReferer($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());

        return $simpleSAMLAuth;
    }

    protected function signoutProcess(sfWebRequest $request, $url)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(['Url']);

        $this->getUser()->signOut();

        $simpleSAMLAuth = new SimpleSAML_Auth_Simple(sfConfig::get('app_saml_sp_name', 'default-sp'));

        $simpleSAMLAuth->logout(url_for($url, true), array(), true);

        SimpleSAML_Session::getSessionFromRequest()->cleanup();
    }
}
