<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementRfpQuestionnaire;

class ConsultantManagementRfpQuestionnaireOption extends Model
{
    protected $table = 'consultant_management_rfp_questionnaire_options';

    protected $fillable = ['consultant_management_rfp_questionnaire_id', 'text', 'value', 'order'];

    public function questionnaire()
    {
        return $this->belongsTo(ConsultantManagementRfpQuestionnaire::class);
    }

}