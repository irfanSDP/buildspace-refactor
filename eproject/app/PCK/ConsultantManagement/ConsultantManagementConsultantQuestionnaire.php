<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementRfpQuestionnaire;
use PCK\Companies\Company;

class ConsultantManagementConsultantQuestionnaire extends Model
{
    protected $table = 'consultant_management_consultant_questionnaires';

    protected $fillable = ['vendor_category_rfp_id', 'company_id', 'status', 'published_date', 'unpublished_date'];

    const STATUS_UNPUBLISHED = 1;
    const STATUS_PUBLISHED = 2;

    const STATUS_UNPUBLISHED_TEXT = 'Published';
    const STATUS_PUBLISHED_TEXT = 'Unpublished';

    protected static function boot()
    {
        parent::boot();
    }
    
    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function getStatusText()
    {
        switch($this->status)
        {
            case self::STATUS_UNPUBLISHED:
                return self::STATUS_UNPUBLISHED_TEXT;
            case self::STATUS_PUBLISHED:
                return self::STATUS_PUBLISHED_TEXT;
            default:
                throw new \Exception('Invalid status');
        }
    }

    public function generalQuestions()
    {
        return ConsultantManagementQuestionnaire::select('consultant_management_questionnaires.*')
        ->whereRaw("NOT EXISTS (
            SELECT 1
            FROM consultant_management_exclude_questionnaires
            WHERE consultant_management_exclude_questionnaires.consultant_management_questionnaire_id = consultant_management_questionnaires.id
            AND consultant_management_exclude_questionnaires.vendor_category_rfp_id = ".$this->vendor_category_rfp_id."
            AND consultant_management_exclude_questionnaires.company_id = ".$this->company_id."
        )")
        ->where('consultant_management_questionnaires.consultant_management_contract_id', '=', $this->consultantManagementVendorCategoryRfp->consultant_management_contract_id)
        ->orderBy('consultant_management_questionnaires.created_at', 'DESC')
        ->get();
    }

    public function rfpQuestions()
    {
        return ConsultantManagementRfpQuestionnaire::select('consultant_management_rfp_questionnaires.*')
        ->where('consultant_management_rfp_questionnaires.vendor_category_rfp_id', '=', $this->vendor_category_rfp_id)
        ->where('consultant_management_rfp_questionnaires.company_id', '=', $this->company_id)
        ->orderBy('consultant_management_rfp_questionnaires.created_at', 'DESC')
        ->get();
    }
}