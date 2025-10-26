<?php

use PCK\Buildspace\ContractManagementVerifier;
use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;
use PCK\Users\User;

class PostContractLetterOfAwardController extends \BaseController {

    public function index(Project $project)
    {
        $verifierRecords = ContractManagementVerifier::getRecordList($project, PostContractClaim::TYPE_LETTER_OF_AWARD, true);

        return View::make('contractManagement.letterOfAward.index', array(
            'project'         => $project,
            'verifierRecords' => $verifierRecords,
        ));
    }

    public function substituteAndApprove(Project $project, $currentVerifierUserId)
    {
        $currentVerifier = User::find($currentVerifierUserId);
        $currentUser     = Confide::user();

        \Log::info("Approving Letter of Award for project [id: {$project->id}] for {$currentVerifier->name} [id: {$currentVerifier->id}] as {$currentUser->name} [id: {$currentUser->id}]");

        ContractManagementVerifier::verifyAsSubstitute($project, $currentVerifier, PostContractClaim::TYPE_LETTER_OF_AWARD, true, $currentUser);

        ContractManagementVerifier::sendNotifications($project, PostContractClaim::TYPE_LETTER_OF_AWARD);

        // If all verifiers have approved.
        if( ContractManagementVerifier::isApproved($project, PostContractClaim::TYPE_LETTER_OF_AWARD) )
        {
            Queue::push('PCK\QueueJobs\UpdateLetterOfAwardStatus', array( 'project' => $project, 'approved' => true, 'userId' => $currentUser->id ));
        }

        Flash::success(trans('contractManagement.approved'));

        return Redirect::back();
    }

    public function substituteAndReject(Project $project, $currentVerifierUserId)
    {
        $currentVerifier = User::find($currentVerifierUserId);
        $currentUser     = Confide::user();

        \Log::info("Rejecting Letter of Award for project [id: {$project->id}] for {$currentVerifier->name} [id: {$currentVerifier->id}] as {$currentUser->name} [id: {$currentUser->id}]");

        ContractManagementVerifier::verifyAsSubstitute($project, $currentVerifier, PostContractClaim::TYPE_LETTER_OF_AWARD, false, $currentUser);

        ContractManagementVerifier::sendNotifications($project, PostContractClaim::TYPE_LETTER_OF_AWARD);

        Flash::success(trans('contractManagement.rejected'));

        return Redirect::back();
    }

}