<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\Companies\Company;

class ConsultantManagementExcludeQuestionnaire extends Model
{
    protected $table = 'consultant_management_exclude_questionnaires';

    protected $fillable = ['consultant_management_questionnaire_id', 'vendor_category_rfp_id', 'company_id'];

    public function consultantManagementQuestionnaire()
    {
        return $this->belongsTo(ConsultantManagementQuestionnaire::class, 'consultant_management_questionnaire_id');
    }

    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}