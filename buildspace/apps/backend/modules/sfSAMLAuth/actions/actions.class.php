<?php

class sfSAMLAuthActions extends BuildspaceSAMLAuthActions
{
    public function executeSignin(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        // ---- 1) Dev bypass (from app.yml: app_auth_saml_enabled) ----------
        // Set dev->auth->saml_enabled: false to skip SAML locally.
        if (!sfConfig::get('app_auth_saml_enabled', true)) {
            // Pick a local user to auto-login (adjust as you like)
            // Prefer admin by email; fallback to id=1
            $u = Doctrine_Query::create()
                ->from('sfGuardUser u')
                ->where('u.email_address = ?', 'admin@buildspace.com')
                ->fetchOne();

            if (!$u) {
                $u = Doctrine_Core::getTable('sfGuardUser')->find(1);
            }

            if ($u) {
                $this->getUser()->signIn($u, true);
                $this->getUser()->clearCredentials();
                $this->getUser()->addCredential('admin');
                return $this->forward('default', 'index');
            }

            // If no user found, at least don’t loop:
            return $this->renderText('Dev SAML bypass is ON but no local user to sign in.');
        }

        // ---- 2) Normal SAML path (prod) -----------------------------------
        $simpleSAMLAuth = $this->signinProcess($request, true);

        if ($simpleSAMLAuth instanceof SimpleSAML_Auth_Simple) {
            // Already authenticated on both sides? Go home.
            if ($simpleSAMLAuth->isAuthenticated() && $this->getUser()->isAuthenticated()) {
                return $this->forward('default', 'index');
            }

            // Force IdP + ReturnTo (same as your current logic)
            $idpEntityId = 'https://auth.buildspace.local/saml2/idp/metadata.php';
            $returnTo    = 'https://bq.buildspace.local/backend_dev.php';

            $loginUrl = $simpleSAMLAuth->getLoginURL($returnTo, [
                'saml:idp' => $idpEntityId,
            ]);

            return $this->redirect($loginUrl);
        }

        return sfView::NONE;
    }

    public function executeSignout(sfWebRequest $request)
    {
        // If SAML is disabled, just clear Symfony session and go home
        if (!sfConfig::get('app_auth_saml_enabled', true)) {
            $this->getUser()->signOut();
            $this->getUser()->setAuthenticated(false);
            $this->getUser()->clearCredentials();
            $this->redirect('@homepage');
            return sfView::NONE;
        }

        // Otherwise do the normal SAML logout flow
        $this->signoutProcess($request, '@homepage');
    }
}
