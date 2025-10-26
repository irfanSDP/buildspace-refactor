<?php

class approvalActions extends BaseActions
{
    public function executeApproveLetterOfAward(sfWebRequest $request)
    {
        $user = $this->getUser()->getGuardUser();

        $moduleIdentifier = $request->getParameter('module_identifier');

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            $project->type == ProjectStructure::TYPE_ROOT and
            ContractManagementVerifierTable::isCurrentVerifier($user, $project, $moduleIdentifier)
        );
        
        $form    = new CsrfForm();
        $success = false;
        $errors  = null;

        if( $this->isFormValid($request, $form) )
        {
            $approved = $request->getParameter('approve') == 'true' ? true : false;

            $remarks = trim($request->getParameter('remarks'));

            ContractManagementVerifierTable::approve($project, $user, $moduleIdentifier, $approved, $remarks);
            ContractManagementVerifierTable::sendNotifications($project, $moduleIdentifier);

            $this->followUpLetterOfAwardApproval($project, $moduleIdentifier);

            $success = true;
        }
        else
        {
            $errors = $form->getErrors();
        }

        return $this->renderJson(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }

    protected function followUpLetterOfAwardApproval(ProjectStructure $project, $moduleIdentifier)
    {
        if(! ContractManagementVerifierTable::isApproved($project, $moduleIdentifier) ) return;

        // Do nothing in regards to letter of award.
        // The status is determined by the verifiers;
        // If all of them have approved, the letter of award is understood to have been submitted.
        // Push to post contract.
        $publishToPostContractOptions = PublishToPostContractOptionTable::findByProjectId($project->id);
        $useOriginalRate              = ( $publishToPostContractOptions->rate_type == PublishToPostContractOption::RATE_TYPE_ESTIMATE ) ? true : false;
        $withoutNotListedItem         = ! $publishToPostContractOptions->with_not_listed_item;
        $usersAssignedManually        = $publishToPostContractOptions->assign_users_manually;

        $this->getRequest()->setParameter('id', $project->id);
        $this->getRequest()->setParameter('usersAssignedManually', $usersAssignedManually);
        $this->getRequest()->setParameter('use_original_rate', $useOriginalRate);
        $this->getRequest()->setParameter('withoutNotListedItem', $withoutNotListedItem);
        $this->getRequest()->setParameter('postContractType', PostContract::PUBLISHED_TYPE_NEW);

        $this->forward('tendering', 'publishToPostContract');
    }

    public function executeApproveClaim(sfWebRequest $request)
    {
        $user = $this->getUser()->getGuardUser();

        $moduleIdentifier = $request->getParameter('module_identifier');
        $objectId = $request->getParameter('object_id');

        $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('project_id')) and
            ContractManagementClaimVerifierTable::isCurrentVerifier($user, $project, $moduleIdentifier, $objectId)
        );

        $form     = new CsrfForm();
        $success  = false;
        $errors   = null;
        $approved = $request->getParameter('approve') == 'true' ? true : false;

        if( $moduleIdentifier == PostContractClaim::TYPE_CLAIM_CERTIFICATE && ! $approved && ( ! is_null($project->PostContract->getInProgressClaimRevision()) ) )
        {
            return $this->renderJson(array(
                'success' => false,
                'errors'  => 'A new claim certificate is in progress. This claim certificate is now locked and cannot be rejected.'
            ));
        }

        if( $this->isFormValid($request, $form) )
        {
            $remarks = trim($request->getParameter('remarks'));

            ContractManagementClaimVerifierTable::approve($project, $user, $moduleIdentifier, $objectId, $approved, $remarks);
            ContractManagementClaimVerifierTable::sendNotifications($project, $moduleIdentifier, $objectId);

            $success = $this->followUpClaimApproval($moduleIdentifier, $objectId);
        }
        else
        {
            $errors = $form->getErrors();
        }

        return $this->renderJson(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }

    protected function followUpClaimApproval($moduleIdentifier, $objectId)
    {
        switch($moduleIdentifier)
        {
            case PostContractClaim::TYPE_ADVANCED_PAYMENT:
            case PostContractClaim::TYPE_WATER_DEPOSIT:
            case PostContractClaim::TYPE_DEPOSIT:
            case PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM:
            case PostContractClaim::TYPE_PURCHASE_ON_BEHALF:
            case PostContractClaim::TYPE_WORK_ON_BEHALF:
            case PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE:
            case PostContractClaim::TYPE_PENALTY:
            case PostContractClaim::TYPE_PERMIT:
            case PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                $postContractClaim = Doctrine_Core::getTable('PostContractClaim')->find($objectId);
                $postContractClaim->onClaimReview();
                break;
            case PostContractClaim::TYPE_VARIATION_ORDER:
                $variationOrder = Doctrine_Core::getTable('VariationOrder')->find($objectId);
                $variationOrder->onClaimReview();
                break;
            case PostContractClaim::TYPE_CLAIM_CERTIFICATE:
                $claimCertificate = Doctrine_Core::getTable('ClaimCertificate')->find($objectId);
                $claimCertificate->onClaimReview();
                break;
            default:
                throw new Exception('Invalid module');
        }

        return true;
    }

}
