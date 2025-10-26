<?php
namespace PCK\ContractorQuestionnaire;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ContractorQuestionnaire\Question;

class Option extends Model
{
    protected $table = 'contractor_questionnaire_options';

    protected $fillable = ['contractor_questionnaire_question_id', 'text', 'value', 'order'];

    public function questionnaire()
    {
        return $this->belongsTo(Question::class, 'contractor_questionnaire_question_id');
    }

}