<?php

class sfSAMLAuthActions extends BuildspaceSAMLAuthActions
{
    public function executeSignin(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);
       
        $simpleSAMLAuth = $this->signinProcess($request, false);

        if($simpleSAMLAuth && is_object($simpleSAMLAuth) && $simpleSAMLAuth instanceof SimpleSAML_Auth_Simple)
        {
            if($simpleSAMLAuth->isAuthenticated() && $this->getUser()->isAuthenticated())
            {
                $eproject = Doctrine_Core::getTable('EProjectProject')->find(intval($request->getParameter('oid')));

                if($eproject and $this->getUser()->getGuardUser()->canAccessEditorByProject($eproject->BuildspaceProjectMainInfo->ProjectStructure))
                {
                    $this->eproject = $eproject;
                    $this->setTemplate('index', 'default');

                    return sfView::SUCCESS;
                }
                else
                {
                    return $this->redirect('@no_access');
                }
            }

            $this->url_idp = $simpleSAMLAuth->login();
        }
        
        return sfView::NONE;
    }

    public function executeSignout(sfWebRequest $request)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(array( 'Url' ));

        $eproject = Doctrine_Core::getTable('EProjectProject')->find(intval($request->getParameter('oid')));
        $url = $this->getContext()->getConfiguration()->generateEditorUrl('homepage', ['oid'=>$eproject->id], true);

        $this->signoutProcess($request, $url);
    }
}
