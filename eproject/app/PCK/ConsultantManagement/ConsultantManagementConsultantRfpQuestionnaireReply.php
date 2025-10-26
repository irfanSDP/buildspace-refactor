<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementRfpQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaire;

class ConsultantManagementConsultantRfpQuestionnaireReply extends Model
{
    protected $table = 'consultant_management_consultant_rfp_questionnaire_replies';

    protected $fillable = ['consultant_management_rfp_questionnaire_id', 'consultant_management_consultant_questionnaire_id', 'text', 'consultant_management_questionnaire_option_id'];

    protected static function boot()
    {
        parent::boot();
    }
    
    public function consultantManagementRfpQuestionnaire()
    {
        return $this->belongsTo(ConsultantManagementRfpQuestionnaire::class, 'consultant_management_rfp_questionnaire_id');
    }

    public function consultantManagementConsultantQuestionnaire()
    {
        return $this->belongsTo(ConsultantManagementConsultantQuestionnaire::class, 'consultant_management_consultant_questionnaire_id');
    }
}