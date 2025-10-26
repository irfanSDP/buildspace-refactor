<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimWorkOnBehalfController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_WORK_ON_BEHALF;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimWorkOnBehalfFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.workOnBehalf.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.workOnBehalf.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-wrench',
            ),
        ));
    }

}