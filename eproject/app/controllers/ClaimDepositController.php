<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimDepositController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_DEPOSIT;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimDepositFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.deposit.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.deposit.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-arrow-left',
            ),
        ));
    }

}