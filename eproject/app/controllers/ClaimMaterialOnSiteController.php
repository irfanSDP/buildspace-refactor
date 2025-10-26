<?php

use PCK\Buildspace\PostContractClaim;
use PCK\Projects\Project;

class ClaimMaterialOnSiteController extends PostContractClaimController {

    protected $moduleIdentifier = PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE;

    public function index(Project $project)
    {
        return View::make('contractManagement/generic/review', array(
            'project'                   => $project,
            'moduleName'                => PostContractClaim::getModuleName($this->moduleIdentifier),
            'moduleIdentifier'          => $this->moduleIdentifier,
            'verifierRecords'           => $this->getVerifierRecords($project),
            'claimObjects'              => $this->getClaimObjects($project),
            'filterClass'               => get_class(new \PCK\Filters\ClaimMaterialOnSiteFilters()),
            'substituteAndRejectRoute'  => 'contractManagement.materialOnSite.substituteAndReject',
            'substituteAndApproveRoute' => 'contractManagement.materialOnSite.substituteAndApprove',
            'iconClasses'                 => array(
                'fa fa-cubes',
            ),
        ));
    }

}