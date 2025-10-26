<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ConsultantManagement\ConsultantManagementVendorCategoryRfp;
use PCK\ConsultantManagement\ConsultantManagementRfpQuestionnaireOption;
use PCK\Companies\Company;

class ConsultantManagementRfpQuestionnaire extends Model
{
    protected $table = 'consultant_management_rfp_questionnaires';

    protected $fillable = ['vendor_category_rfp_id', 'company_id', 'question', 'type', 'required'];

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
            if($model->type == ConsultantManagementRfpQuestionnaire::TYPE_ATTACHMENT_ONLY)
            {
                $model->with_attachment = true;
            }
        });

        self::saved(function(self $model)
        {
            if($model->type == ConsultantManagementRfpQuestionnaire::TYPE_TEXT or $model->type == ConsultantManagementRfpQuestionnaire::TYPE_ATTACHMENT_ONLY)
            {
                $model->options()->delete();
            }
        });

        static::deleting(function(self $model)
        {
            $model->options()->delete();
        });
    }
    
    public function consultantManagementVendorCategoryRfp()
    {
        return $this->belongsTo(ConsultantManagementVendorCategoryRfp::class, 'vendor_category_rfp_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
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
        return $this->hasMany(ConsultantManagementRfpQuestionnaireOption::class)->orderBy('order', 'asc');
    }

    public function setTypeAttribute(string $type)
    {
        $this->attributes['type'] = strtolower($type);
    }

    public function deletable()
    {
        $count = ConsultantManagementRfpQuestionnaireOption::join('consultant_management_consultant_rfp_questionnaire_replies', 'consultant_management_consultant_rfp_questionnaire_replies.consultant_management_rfp_questionnaire_option_id', '=', 'consultant_management_rfp_questionnaire_options.id')
        ->where('consultant_management_rfp_questionnaire_options.consultant_management_rfp_questionnaire_id', '=', $this->id)
        ->count();

        return (!$count);
    }

    public function getConsultantReply(ConsultantManagementConsultantQuestionnaire $consultantQuestionnaire)
    {
        switch($this->type)
        {
            case self::TYPE_TEXT:
                return ConsultantManagementConsultantRfpQuestionnaireReply::where('consultant_management_rfp_questionnaire_id', '=', $this->id)
                ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                ->first();
            case self::TYPE_MULTI_SELECT:
                return ConsultantManagementConsultantRfpQuestionnaireReply::where('consultant_management_rfp_questionnaire_id', '=', $this->id)
                ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                ->get();
            case self::TYPE_SINGLE_SELECT:
                return ConsultantManagementConsultantRfpQuestionnaireReply::where('consultant_management_rfp_questionnaire_id', '=', $this->id)
                ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
                ->first();
            default:
                return null;
        }
    }

    public function getConsultantReplySubmittedDate(ConsultantManagementConsultantQuestionnaire $consultantQuestionnaire)
    {
        $reply = ConsultantManagementConsultantRfpQuestionnaireReply::where('consultant_management_rfp_questionnaire_id', '=', $this->id)
        ->where('consultant_management_consultant_questionnaire_id', '=', $consultantQuestionnaire->id)
        ->first();

        return ($reply) ? $reply->created_at : null;
    }
}