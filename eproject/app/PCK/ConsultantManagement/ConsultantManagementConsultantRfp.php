<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementSubsidiary;

class ConsultantManagementConsultantRfp extends Model
{
    protected $table = 'consultant_management_consultant_rfp';

    protected $fillable = ['consultant_management_rfp_revision_id', 'company_id', 'awarded'];

    public function consultantManagementRfpRevision()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementRfpRevision', 'consultant_management_rfp_revision_id');
    }

    public function company()
    {
        return $this->belongsTo('PCK\Companies\Company', 'company_id');
    }

    public function commonInformation()
    {
        return $this->hasOne('PCK\ConsultantManagement\ConsultantManagementConsultantRfpCommonInformation', 'consultant_management_consultant_rfp_id');
    }

    public function consultantProposedFees()
    {
        return $this->hasMany('PCK\ConsultantManagement\ConsultantManagementConsultantRfpProposedFee', 'consultant_management_consultant_rfp_id');
    }

    public function getConsultantProposedFeeBySubsidiary(ConsultantManagementSubsidiary $consultantManagementSubsidiary)
    {
        return ConsultantManagementConsultantRfpProposedFee::where('consultant_management_consultant_rfp_id', '=', $this->id)
        ->where('consultant_management_subsidiary_id', '=', $consultantManagementSubsidiary->id)
        ->first();
    }
}