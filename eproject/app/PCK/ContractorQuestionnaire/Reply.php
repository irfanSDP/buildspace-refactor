<?php
namespace PCK\ContractorQuestionnaire;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ContractorQuestionnaire\Question;

class Reply extends Model
{
    protected $table = 'contractor_questionnaire_replies';

    protected $fillable = ['contractor_questionnaire_question_id', 'text', 'contractor_questionnaire_option_id'];

    protected static function boot()
    {
        parent::boot();
    }
    
    public function question()
    {
        return $this->belongsTo(Question::class, 'contractor_questionnaire_question_id');
    }
}