<?php

class sfSAMLAuthActions extends BuildspaceSAMLAuthActions
{
    public function executeSignin(sfWebRequest $request)
    {
        sfConfig::set('sf_web_debug', false);

        $simpleSAMLAuth = $this->signinProcess($request, false);

        if($simpleSAMLAuth && is_object($simpleSAMLAuth) && $simpleSAMLAuth instanceof SimpleSAML_Auth_Simple)
        {
            $user = $this->getUser();

            if($simpleSAMLAuth->isAuthenticated() && $user->isAuthenticated())
            {
                if(!$request->hasParameter('id')) return $this->redirect('@no_access');

                if($request->hasParameter('isMaster') && $request->getParameter('isMaster') == true)
                {
                    $masterCostData = Doctrine_Core::getTable('MasterCostData')->find(intval($request->getParameter('id')));

                    if( $masterCostData and $user->getGuardUser()->canAccessMasterCostData($masterCostData->id) )
                    {
                        $masterCostData = array(
                            'id' => $masterCostData->id,
                            'title' => $masterCostData->name
                        );

                        $this->masterCostData = $masterCostData;
                        $this->setTemplate('index', 'default');

                        return sfView::SUCCESS;
                    }
                }
                else
                {
                    $costData = Doctrine_Core::getTable('CostData')->find(intval($request->getParameter('id')));

                    $accessStatus = $user->getGuardUser()->canAccessCostData($costData->id);

                    if( $accessStatus['canAccess'] )
                    {
                        $form = new BaseForm();

                        $costData = array(
                            'id'            => $costData->id,
                            'name'          => $costData->name,
                            'awarded_date'  => $costData->awarded_date ? date('d-m-Y', strtotime($costData->awarded_date)) : null,
                            'approved_date' => $costData->approved_date ? date('d-m-Y', strtotime($costData->approved_date)) : null,
                            'adjusted_date' => $costData->adjusted_date ? date('d-m-Y', strtotime($costData->adjusted_date)) : null,
                            'class'         => get_class($costData),
                            '_csrf_token'   => $form->getCSRFToken(),
                            'isEditor'      => $accessStatus['isEditor']
                        );

                        $this->costData    = $costData;
                        $this->isEditor    = $accessStatus['isEditor'];
                        $this->setTemplate('costData', 'default');

                        return sfView::SUCCESS;
                    }
                }

                return $this->redirect('@no_access');
            }

            $this->url_idp = $simpleSAMLAuth->login();
        }

        return sfView::NONE;
    }

    public function executeSignout(sfWebRequest $request)
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers(['Url']);

        $url = $this->getContext()->getConfiguration()->generateCostDataUrl('cost_data', array(), true);

        $this->signoutProcess($request, $url);
    }
}
