<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimOutOfContractItemsController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimOutOfContractItemFilters),
            'substituteAndRejectRoute'  => 'contractManagement.outOfContractItems.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.outOfContractItems.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-user-secret',
            ),
        ));
    }

}