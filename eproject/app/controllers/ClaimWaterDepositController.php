<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimWaterDepositController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_WATER_DEPOSIT;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimWaterDepositFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.waterDeposit.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.waterDeposit.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-tint',
            ),
        ));
    }

}