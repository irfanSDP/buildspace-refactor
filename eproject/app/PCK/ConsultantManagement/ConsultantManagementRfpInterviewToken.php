<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\ConsultantManagement\ConsultantManagementRfpInterviewConsultant;

class ConsultantManagementRfpInterviewToken extends Model
{
    protected $table = 'consultant_management_rfp_interview_tokens';

    protected $fillable = ['consultant_management_rfp_interview_consultant_id', 'token'];

    public function consultantManagementRfpInterviewConsultant()
    {
        return $this->belongsTo(ConsultantManagementRfpInterviewConsultant::class, 'consultant_management_rfp_interview_consultant_id');
    }
}