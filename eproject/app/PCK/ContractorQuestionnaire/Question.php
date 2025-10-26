<?php
namespace PCK\ContractorQuestionnaire;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ContractorQuestionnaire\Questionnaire;
use PCK\ContractorQuestionnaire\Option;
use PCK\ContractorQuestionnaire\Reply;

class Question extends Model
{
    protected $table = 'contractor_questionnaire_questions';

    protected $fillable = ['contractor_questionnaire_id', 'question', 'type', 'required'];

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
            if($model->type == Question::TYPE_ATTACHMENT_ONLY)
            {
                $model->with_attachment = true;
            }
        });

        self::saved(function(self $model)
        {
            if($model->type == Question::TYPE_TEXT or $model->type == Question::TYPE_ATTACHMENT_ONLY)
            {
                $model->options()->delete();
            }
        });

        self::deleting(function(self $model)
        {
            $model->options()->delete();
        });
    }
    
    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class, 'contractor_questionnaire_id');
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
        return $this->hasMany(Option::class, 'contractor_questionnaire_question_id')->orderBy('order', 'asc');
    }

    public function setTypeAttribute(string $type)
    {
        $this->attributes['type'] = strtolower($type);
    }

    public function deletable()
    {
        $count = Question::join('contractor_questionnaire_replies', 'contractor_questionnaire_replies.contractor_questionnaire_question_id', '=', 'contractor_questionnaire_questions.id')
        ->where('contractor_questionnaire_questions.id', '=', $this->id)
        ->count();

        return (!$count) && $this->questionnaire->editable();
    }

    public function getReply()
    {
        switch($this->type)
        {
            case self::TYPE_TEXT:
                return Reply::where('contractor_questionnaire_question_id', '=', $this->id)
                ->first();
            case self::TYPE_MULTI_SELECT:
                return Reply::where('contractor_questionnaire_question_id', '=', $this->id)
                ->get();
            case self::TYPE_SINGLE_SELECT:
                return Reply::where('contractor_questionnaire_question_id', '=', $this->id)
                ->first();
            default:
                return null;
        }
    }

    public function getReplySubmittedDate()
    {
        $reply = Reply::where('contractor_questionnaire_question_id', '=', $this->id)
        ->first();
        
        return ($reply) ? $reply->created_at : null;
    }
}