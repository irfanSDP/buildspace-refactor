<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ApprovalDocument;
use PCK\ConsultantManagement\LetterOfAward;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementRfpRevision;
use PCK\ConsultantManagement\ConsultantManagementRfpInterview;
use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultant;
use PCK\ConsultantManagement\ConsultantManagementRecommendationOfConsultantCompany;
use PCK\ConsultantManagement\ConsultantManagementRfpAttachmentSetting;
use PCK\VendorCategory\VendorCategory;

use PCK\Buildspace\AccountCode as BsAccountCode;

class ConsultantManagementVendorCategoryRfp extends Model
{
    protected $table = 'consultant_management_vendor_categories_rfp';

    protected $fillable = ['vendor_category_id', 'consultant_management_contract_id', 'cost_type'];

    const COST_TYPE_CONSTRUCTION_COST = 1;
    const COST_TYPE_LANDSCAPE_COST = 2;
    const COST_TYPE_LUMP_SUM_COST = 4;

    const COST_TYPE_CONSTRUCTION_COST_TEXT = 'Total Construction Cost';
    const COST_TYPE_LANDSCAPE_COST_TEXT = 'Total Landscape Cost';
    const COST_TYPE_LUMP_SUM_COST_TEXT = 'Lump Sum Cost';

    const STATUS_RECOMMENDATION_OF_CONSULTANT = 2;
    const STATUS_LIST_OF_CONSULTANT = 4;
    const STATUS_CALLING_RFP = 8;
    const STATUS_CLOSED_RFP = 16;
    const STATUS_APPROVED = 32;
    const STATUS_AWARDED = 64;

    protected static function boot()
    {
        parent::boot();

        self::deleting(function(self $model)
        {
            if($model->recommendationOfConsultant)
            {
                $model->recommendationOfConsultant->delete();
            }

            $model->recommendationOfConsultantCompanies()->delete();
            $model->revisions()->delete();
            $model->rfpAttachmentSettings()->delete();
            $model->rfpInterviews()->delete();
        });
    }

    public function consultantManagementContract()
    {
        return $this->belongsTo(ConsultantManagementContract::class, 'consultant_management_contract_id');
    }

    public function vendorCategory()
    {
        return $this->belongsTo(VendorCategory::class, 'vendor_category_id');
    }

    public function recommendationOfConsultant()
    {
        return $this->hasOne(ConsultantManagementRecommendationOfConsultant::class, 'vendor_category_rfp_id');
    }

    public function recommendationOfConsultantCompanies()
    {
        return $this->hasMany(ConsultantManagementRecommendationOfConsultantCompany::class, 'vendor_category_rfp_id');
    }
    
    public function revisions()
    {
        return $this->hasMany(ConsultantManagementRfpRevision::class, 'vendor_category_rfp_id');
    }

    public function rfpAttachmentSettings()
    {
        return $this->hasMany(ConsultantManagementRfpAttachmentSetting::class, 'vendor_category_rfp_id');
    }

    public function rfpInterviews()
    {
        return $this->hasMany(ConsultantManagementRfpInterview::class, 'vendor_category_rfp_id');
    }

    public function approvalDocument()
    {
        return $this->hasOne(ApprovalDocument::class, 'vendor_category_rfp_id');
    }

    public function letterOfAward()
    {
        return $this->hasOne(LetterOfAward::class, 'vendor_category_rfp_id');
    }

    public function getLatestRfpRevision()
    {
        return ConsultantManagementRfpRevision::where('vendor_category_rfp_id', '=', $this->id)
        ->orderBy('revision', 'desc')
        ->first();
    }

    public function getCostTypeText()
    {
        switch($this->cost_type)
        {
            case self::COST_TYPE_CONSTRUCTION_COST:
                return self::COST_TYPE_CONSTRUCTION_COST_TEXT;
            case self::COST_TYPE_LANDSCAPE_COST:
                return self::COST_TYPE_LANDSCAPE_COST_TEXT;
            case self::COST_TYPE_LUMP_SUM_COST:
                return self::COST_TYPE_LUMP_SUM_COST_TEXT;
            default:
                throw new \Exception('Invalid cost type');
        }
    }

    public function getStatusText()
    {
        if($this->revisions && !empty($this->revisions->count()))
        {
            $latestRevision = $this->getLatestRfpRevision();
            $callingRfp = ($latestRevision) ? $latestRevision->callingRfp : null;

            if($callingRfp)
            {
                if(!$callingRfp->isCallingRFpStillOpen())
                {
                    if($approvalDocument = $this->approvalDocument && $this->approvalDocument->status == ApprovalDocument::STATUS_APPROVED)
                    {
                        if($letterOfAward = $this->letterOfAward && $this->letterOfAward->status == LetterOfAward::STATUS_APPROVED)
                        {
                            return trans('general.awarded');
                        }

                        return trans('verifiers.approved');
                    }

                    return trans('general.closedRFP');
                }

                return ($callingRfp->status == ConsultantManagementCallingRfp::STATUS_APPROVED) ? trans('general.callingRFP') : trans('general.closedRFP') ;
            }

            return 'LOC';
        }
        elseif(!$this->recommendationOfConsultant || (!empty($this->recommendationOfConsultant->count()) && (!$this->revisions || empty($this->revisions->count()))))
        {
            return 'ROC';
        }
        else
        {
            return trans('forms.draft');
        }
    }
    
    public function editable()
    {
        return (!$this->recommendationOfConsultant or $this->recommendationOfConsultant->status == ConsultantManagementRecommendationOfConsultant::STATUS_DRAFT);
    }

    public function deletable()
    {
        return (!$this->recommendationOfConsultant or $this->recommendationOfConsultant->status == ConsultantManagementRecommendationOfConsultant::STATUS_DRAFT);
    }

    public function accountCodePivotRecords()
    {
        return $this->hasMany(ConsultantManagementVendorCategoryRfpAccountCode::class, 'vendor_category_rfp_id');
    }

    public function getBsAccountCodes()
    {
        return BsAccountCode::whereIn('id', $this->accountCodePivotRecords->lists('account_code_id'))
            ->get();
    }
}