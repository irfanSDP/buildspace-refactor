<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimAdvancedPaymentController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_ADVANCED_PAYMENT;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimAdvancedPaymentFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.advancedPayment.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.advancedPayment.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-fast-forward',
            ),
        ));
    }

}