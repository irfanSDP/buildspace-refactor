<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimPurchaseOnBehalfController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_PURCHASE_ON_BEHALF;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimPurchaseOnBehalfFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.purchaseOnBehalf.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.purchaseOnBehalf.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-shopping-bag',
            ),
        ));
    }

}