<?php
namespace PCK\ConsultantManagement;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ConsultantManagementConsultantRfpProposedFee extends Model
{
    protected $table = 'consultant_management_consultant_rfp_proposed_fees';

    protected $fillable = ['consultant_management_consultant_rfp_id', 'consultant_management_subsidiary_id', 'proposed_fee_percentage'];

    public function consultantManagementConsultantRfp()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementConsultantRfp', 'consultant_management_consultant_rfp_id');
    }

    public function consultantManagementSubsidiary()
    {
        return $this->belongsTo('PCK\ConsultantManagement\ConsultantManagementSubsidiary', 'consultant_management_subsidiary_id');
    }
}