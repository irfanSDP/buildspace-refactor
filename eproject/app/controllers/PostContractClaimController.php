<?php

use Illuminate\Database\Eloquent\Collection;
use PCK\Buildspace\ClaimCertificate;
use PCK\Buildspace\ContractManagementClaimVerifier;
use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;
use PCK\Users\User;

class PostContractClaimController extends \BaseController {

    protected $moduleIdentifier;

    protected function getVerifierRecords(Project $project)
    {
        $allVerifierRecords = ContractManagementClaimVerifier::getRecordList($project, $this->moduleIdentifier, true);

        $objectIds = $allVerifierRecords->lists('object_id');

        $class = ContractManagementClaimVerifier::getModuleClass($this->moduleIdentifier);

        $verifierRecords = array();

        foreach($objectIds as $objectId)
        {
            if( array_key_exists($objectId, $verifierRecords) ) continue;

            if( ! $claimObject = $class::find($objectId) ) continue;

            $objectVerifiers = $allVerifierRecords->filter(function($record) use ($objectId)
            {
                return $record->object_id == $objectId;
            });

            $verifierRecords[ $objectId ] = $objectVerifiers;
        }

        return $verifierRecords;
    }

    protected function getClaimObjects(Project $project)
    {
        $allVerifierRecords = ContractManagementClaimVerifier::getRecordList($project, $this->moduleIdentifier, true);

        $objectIds = $allVerifierRecords->lists('object_id');

        $verifierRecords = array();
        $claimObjects    = array();

        $class = ContractManagementClaimVerifier::getModuleClass($this->moduleIdentifier);

        foreach($objectIds as $objectId)
        {
            if( array_key_exists($objectId, $verifierRecords) ) continue;

            if( ! $claimObject = $class::find($objectId) ) continue;

            $objectVerifiers = $allVerifierRecords->filter(function($record) use ($objectId)
            {
                return $record->object_id == $objectId;
            });

            $verifierRecords[ $objectId ] = $objectVerifiers;
            $claimObjects[ $objectId ]    = $claimObject;
        }

        $claimObjects = new Collection($claimObjects);

        switch(get_class($class))
        {
            case get_class(new ClaimCertificate):
                $statusOrder = array(
                    ClaimCertificate::STATUS_TYPE_PENDING_FOR_APPROVAL,
                    ClaimCertificate::STATUS_TYPE_APPROVED,
                    ClaimCertificate::STATUS_TYPE_REJECTED,
                );
                break;
            case get_class(new \PCK\Buildspace\VariationOrder):
            case get_class(new PostContractClaim):
                $statusOrder = array(
                    PostContractClaim::STATUS_PENDING,
                    PostContractClaim::STATUS_APPROVED,
                );
                break;
            default:
                throw new Exception('Invalid class');
        }

        \PCK\Helpers\Sorter::multiSort($claimObjects, array(

            array(
                'attribute' => 'status',
                'order'     => $statusOrder,
            ),
            array(
                'attribute' => 'id',
                'sort'      => 'desc',
            ),


        ));

        return $claimObjects;
    }

    public function substituteAndApprove(Project $project, $currentVerifierUserId, $objectId)
    {
        $transaction = new \PCK\Helpers\DBTransaction(array( 'buildspace' ));
        $transaction->begin();

        try
        {
            $currentVerifier = User::find($currentVerifierUserId);
            $currentUser     = Confide::user();

            $moduleName = PostContractClaim::getModuleName($this->moduleIdentifier);

            \Log::info("Approving {$moduleName} for project [id: {$project->id}] for {$currentVerifier->name} [id: {$currentVerifier->id}] as {$currentUser->name} [id: {$currentUser->id}]");

            ContractManagementClaimVerifier::verifyAsSubstitute($project, $currentVerifier, $this->moduleIdentifier, $objectId, true, $currentUser);

            $moduleClass = ContractManagementClaimVerifier::getModuleClass($this->moduleIdentifier);

            $claim = $moduleClass::find($objectId);
            $claim->onReview($project, $this->moduleIdentifier);

            ContractManagementClaimVerifier::sendNotifications($project, $this->moduleIdentifier, $objectId);

            $transaction->commit();

            $claim->updateClaimCertificate($currentUser);

            Flash::success(trans('contractManagement.approved'));
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            Log::error("Approval failed [id: {$objectId}; module: {$this->moduleIdentifier} ({$moduleName})] => {$e->getMessage()}");
            Flash::error(trans('contractManagement.reviewFailed'));
        }

        return Redirect::back();
    }

    public function substituteAndReject(Project $project, $currentVerifierUserId, $objectId)
    {
        if( $this->moduleIdentifier == PostContractClaim::TYPE_CLAIM_CERTIFICATE && ( ! is_null($project->getBsProjectMainInformation()->projectStructure->postContract->getInProgressClaimRevision()) ) )
        {
            Flash::error(trans('contractManagement.cannotBeRejected'));
            return Redirect::back();
        }

        $transaction = new \PCK\Helpers\DBTransaction(array( 'buildspace' ));
        $transaction->begin();

        try
        {
            $currentVerifier = User::find($currentVerifierUserId);
            $currentUser     = Confide::user();

            $moduleName = PostContractClaim::getModuleName($this->moduleIdentifier);

            \Log::info("Rejecting {$moduleName} for project [id: {$project->id}] for {$currentVerifier->name} [id: {$currentVerifier->id}] as {$currentUser->name} [id: {$currentUser->id}]");

            ContractManagementClaimVerifier::verifyAsSubstitute($project, $currentVerifier, $this->moduleIdentifier, $objectId, false, $currentUser);

            $moduleClass = ContractManagementClaimVerifier::getModuleClass($this->moduleIdentifier);

            $claim = $moduleClass::find($objectId);
            $claim->onReview($project, $this->moduleIdentifier);

            ContractManagementClaimVerifier::sendNotifications($project, $this->moduleIdentifier, $objectId);

            $transaction->commit();
            Flash::success(trans('contractManagement.rejected'));
        }
        catch(Exception $e)
        {
            $transaction->rollback();
            Log::error("Rejecting failed [id: {$objectId}; module: {$this->moduleIdentifier} ({$moduleName})] => {$e->getMessage()}");
            Flash::error(trans('contractManagement.reviewFailed'));
        }

        return Redirect::back();
    }

}