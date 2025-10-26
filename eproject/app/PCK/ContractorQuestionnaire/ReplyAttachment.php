<?php
namespace PCK\ContractorQuestionnaire;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ContractorQuestionnaire\Question;

class ReplyAttachment extends Model
{
    protected $table = 'contractor_questionnaire_reply_attachments';

    protected $fillable = ['contractor_questionnaire_question_id'];

    protected static function boot()
    {
        parent::boot();
    }
    
    public function question()
    {
        return $this->belongsTo(Question::class, 'contractor_questionnaire_question_id');
    }
    
    public function deletable()
    {
        return true;
    }
}