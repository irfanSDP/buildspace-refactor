<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimVariationOrderController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_VARIATION_ORDER;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimVariationOrderFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.variationOrder.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.variationOrder.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-exchange-alt',
            ),
        ));
    }

}