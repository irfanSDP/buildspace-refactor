<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimPenaltyController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_PENALTY;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimPenaltyFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.penalty.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.penalty.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-gavel',
            ),
        ));
    }

}