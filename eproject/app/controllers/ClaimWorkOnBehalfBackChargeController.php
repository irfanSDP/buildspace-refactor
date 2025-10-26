<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimWorkOnBehalfBackChargeController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimWorkOnBehalfBackChargeFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.workOnBehalfBackCharge.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.workOnBehalfBackCharge.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-arrow-left',
                'fa fa-wrench',
            ),
        ));
    }

}