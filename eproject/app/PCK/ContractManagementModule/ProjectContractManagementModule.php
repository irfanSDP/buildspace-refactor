<?php namespace PCK\ContractManagementModule;

use Illuminate\Database\Eloquent\Model;
use PCK\Buildspace\PostContractClaim;
use PCK\Verifier\Verifiable;

class ProjectContractManagementModule extends Model implements Verifiable {

    protected $fillable = [ 'module_identifier', 'project_id' ];

    public static function getRecord($projectId, $moduleIdentifier)
    {
        return static::firstOrCreate(array( 'project_id' => $projectId, 'module_identifier' => $moduleIdentifier ));
    }

    public static function getModuleNames($moduleIdentifier = null)
    {
        $moduleNames = array(
            PostContractClaim::TYPE_LETTER_OF_AWARD                      => trans('contractManagement.publishToPostContract'),
            PostContractClaim::TYPE_CLAIM_CERTIFICATE                    => trans('contractManagement.claimCertificate'),
            PostContractClaim::TYPE_VARIATION_ORDER                      => trans('contractManagement.variationOrder'),
            PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE => trans('contractManagement.materialOnSite'),
            PostContractClaim::TYPE_DEPOSIT                              => trans('contractManagement.deposit'),
            PostContractClaim::TYPE_OUT_OF_CONTRACT_ITEM                 => trans('contractManagement.outOfContractItems'),
            PostContractClaim::TYPE_PURCHASE_ON_BEHALF                   => trans('contractManagement.purchaseOnBehalf'),
            PostContractClaim::TYPE_ADVANCED_PAYMENT                     => trans('contractManagement.advancedPayment'),
            PostContractClaim::TYPE_WORK_ON_BEHALF                       => trans('contractManagement.workOnBehalf'),
            PostContractClaim::TYPE_WORK_ON_BEHALF_BACK_CHARGE           => trans('contractManagement.workOnBehalfBackCharge'),
            PostContractClaim::TYPE_PENALTY                              => trans('contractManagement.penalty'),
            PostContractClaim::TYPE_PERMIT                               => trans('contractManagement.permit'),
            PostContractClaim::TYPE_WATER_DEPOSIT                        => trans('contractManagement.utilities'),
        );

        if( $moduleIdentifier ) return $moduleNames[ $moduleIdentifier ];

        return $moduleNames;
    }

    public function getOnApprovedView()
    {
    }

    public function getOnRejectedView()
    {
    }

    public function getOnPendingView()
    {
    }

    public function getRoute()
    {
    }

    public function getViewData($locale)
    {
    }

    public function getOnApprovedNotifyList()
    {
    }

    public function getOnRejectedNotifyList()
    {
    }

    public function getOnApprovedFunction()
    {
    }

    public function getOnRejectedFunction()
    {
    }

    public function onReview()
    {
    }

    public function getEmailSubject($locale) 
    {
        return "";
    }

    public function getSubmitterId()
    {
        return null;
    }

    public function getModuleName()
    {
        return trans('modules.projectManagement');
    }
}