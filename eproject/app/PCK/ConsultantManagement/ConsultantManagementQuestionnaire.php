<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementContract;
use PCK\ConsultantManagement\ConsultantManagementQuestionnaireOption;
use PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaire;
use PCK\ConsultantManagement\ConsultantManagementConsultantQuestionnaireReply;

class ConsultantManagementQuestionnaire extends Model
{
    protected $table = 'consultant_management_questionnaires';

    protected $fillable = ['consultant_management_contract_id', 'question', 'type', 'required'];

    const TYPE_TEXT = 1;
    const TYPE_ATTACHMENT_ONLY = 2;
    const TYPE_MULTI_SELECT = 4;
    const TYPE_SINGLE_SELECT = 8;

    const TYPE_TEXT_TEXT = 'Text';
    const TYPE_ATTACHMENT_ONLY_TEXT = 'Attachment Only';
    const TYPE_MULTI_SELECT_TEXT = 'Multi Select';
    const TYPE_SINGLE_SELECT_TEXT = 'Single Select';

    protected static function boot()
    {
        parent::boot();

        self::saving(function(self $model)
        {
            if($model->type == ConsultantManagementQuestionnaire::TYPE_ATTACHMENT_ONLY)
            {
                $model->with_attachment = true;
            }
        });

        self::saved(function(self $model)
        {
            if($model->type == ConsultantManagementQuestionnaire::TYPE_TEXT or $model->type == ConsultantManagementQuestionnaire::TYPE_ATTACHMENT_ONLY)
            {
                $model->options()->delete();
            }
        });

        self::deleting(function(self $model)
        {
            $model->options()->delete();
        });
    }
    
    public function consultantManagementContract()
    {
        return $this->belongsTo(ConsultantManagementContract::class, 'consultant_management_contract_id');
    }

    public function getTypeText()
    {
        switch($this->type)
        {
            case self::TYPE_TEXT:
                return self::TYPE_TEXT_TEXT;
            case self::TYPE_ATTACHMENT_ONLY:
                return self::TYPE_ATTACHMENT_ONLY_TEXT;
            case self::TYPE_MULTI_SELECT:
                return self::TYPE_MULTI_SELECT_TEXT;
            case self::TYPE_SINGLE_SELECT:
                return self::TYPE_SINGLE_SELECT_TEXT;
            default:
                throw new \Exception('Invalid type');
        }
    }

    public function options()
    {
        return $this->hasMany(ConsultantManagementQuestionnaireOption::class)->orderBy('order', 'asc');
    }

    public function setTypeAttribute(string $type)
    {
        $this->attributes['type'] = strtolower($type);
    }

    public function deletable()
    {
        $count = ConsultantManagementQuestionnaire::join('consultant_management_consultant_questionnaire_replies', 'consultant_management_consultant_questionnaire_replies.consultant_management_questionnaire_id', '=', 'consultant_management_questionnaires.id')
        ->where('consultant_management_questionnaires.id', '=', $this->id)
        ->count();

        return (!$count);
    }

    public function getConsultantReply(ConsultantManagementConsultantQuestionnaire $consultantQuestionnaire)
    {
        switch($this->type)
        {
            case self::TYPE_TEXT:
                return ConsultantManagementConsultantQuestionnaireReply::where('consultant_management_questionnaire_id', '=', $this->id)
                ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                ->first();
            case self::TYPE_MULTI_SELECT:
                return ConsultantManagementConsultantQuestionnaireReply::where('consultant_management_questionnaire_id', '=', $this->id)
                ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                ->get();
            case self::TYPE_SINGLE_SELECT:
                return ConsultantManagementConsultantQuestionnaireReply::where('consultant_management_questionnaire_id', '=', $this->id)
                ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                ->first();
            default:
                return null;
        }
    }

    public function getConsultantReplySubmittedDate(ConsultantManagementConsultantQuestionnaire $consultantQuestionnaire)
    {
        $reply = ConsultantManagementConsultantQuestionnaireReply::where('consultant_management_questionnaire_id', '=', $this->id)
        ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
        ->first();
        
        return ($reply) ? $reply->created_at : null;
    }
}