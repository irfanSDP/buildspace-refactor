<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaireReply;

class ConsultantManagementQuestionnaireOption extends Model
{
    protected $table = 'consultant_management_questionnaire_options';

    protected $fillable = ['consultant_management_questionnaire_id', 'text', 'value', 'order'];

    public function questionnaire()
    {
        return $this->belongsTo(ConsultantManagementQuestionnaire::class);
    }

    public function replies()
    {
        return $this->hasMany(ConsultantManagementConsultantQuestionnaireReply::class, 'consultant_management_questionnaire_option_id')->orderBy('order', 'asc');
    }
}