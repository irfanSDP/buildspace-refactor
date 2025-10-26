<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class PostContractClaim extends Model {

    use SoftDeletingTrait;

    protected $connection = 'buildspace';

    protected $table = 'bs_post_contract_claims';

    const TYPE_LETTER_OF_AWARD                      = 1;
    const TYPE_WATER_DEPOSIT                        = 2;
    const TYPE_DEPOSIT                              = 3;
    const TYPE_OUT_OF_CONTRACT_ITEM                 = 4;
    const TYPE_PURCHASE_ON_BEHALF                   = 5;
    const TYPE_ADVANCED_PAYMENT                     = 6;
    const TYPE_WORK_ON_BEHALF                       = 7;
    const TYPE_WORK_ON_BEHALF_BACK_CHARGE           = 8;
    const TYPE_PENALTY                              = 9;
    const TYPE_PERMIT                               = 10;
    const TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE = 11;
    const TYPE_CLAIM_CERTIFICATE                    = 12;
    const TYPE_VARIATION_ORDER                      = 32;

    const STATUS_APPROVED  = 1;
    const STATUS_PENDING   = 2;
    const STATUS_PREPARING = 4;

    public function projectStructure()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'updated_by');
    }

    public function getEprojectUpdatedBy()
    {
        return $this->updatedBy->Profile->getEProjectUser();
    }

    public function getDisplayDescriptionAttribute()
    {
        return $this->description;
    }

    public static function getModuleName($type)
    {
        switch($type)
        {
            case self::TYPE_LETTER_OF_AWARD:
                return trans('contractManagement.publishToPostContract');
            case self::TYPE_WATER_DEPOSIT:
                return trans('contractManagement.waterDeposit');
            case self::TYPE_DEPOSIT:
                return trans('contractManagement.deposit');
            case self::TYPE_OUT_OF_CONTRACT_ITEM:
                return trans('contractManagement.outOfContractItems');
            case self::TYPE_PURCHASE_ON_BEHALF:
                return trans('contractManagement.purchaseOnBehalf');
            case self::TYPE_ADVANCED_PAYMENT:
                return trans('contractManagement.advancedPayment');
            case self::TYPE_WORK_ON_BEHALF:
                return trans('contractManagement.workOnBehalf');
            case self::TYPE_WORK_ON_BEHALF_BACK_CHARGE:
                return trans('contractManagement.workOnBehalfBackCharge');
            case self::TYPE_PENALTY:
                return trans('contractManagement.penalty');
            case self::TYPE_PERMIT:
                return trans('contractManagement.permit');
            case self::TYPE_VARIATION_ORDER:
                return trans('contractManagement.variationOrder');
            case self::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                return trans('contractManagement.materialOnSite');
            case self::TYPE_CLAIM_CERTIFICATE:
                return trans('contractManagement.claimCertificate');
            default:
                throw new \Exception('Invalid module type');
        }
    }

    public function onReview($project, $moduleId)
    {
        if( ContractManagementClaimVerifier::isApproved($project, $moduleId, $this->id) )
        {
            $this->status               = self::STATUS_APPROVED;
            $this->claim_certificate_id = $this->projectStructure->getCurrentClaimCertificate()->id ?? null;
        }
        elseif( ContractManagementClaimVerifier::isRejected($project, $moduleId, $this->id) )
        {
            $this->status = self::STATUS_PREPARING;
        }

        return $this->save();
    }

}