<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimCertificateController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_CLAIM_CERTIFICATE;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimCertificateFilters),
            'substituteAndRejectRoute'  => 'contractManagement.claimCertificate.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.claimCertificate.substituteAndApprove',
            'iconClasses'               => array(
                'fa fa-certificate',
            ),
        ));
    }

}