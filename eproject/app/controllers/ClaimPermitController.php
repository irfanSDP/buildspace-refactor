<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimPermitController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_PERMIT;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimPermitFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.permit.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.permit.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-file-alt',
            ),
        ));
    }

}