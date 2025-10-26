<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Helpers\StringOperations;

use PCK\ConsultantManagement\LetterOfAwardTemplate;

class LetterOfAwardTemplateClause extends Model
{
    protected $table = 'consultant_management_letter_of_award_template_clauses';

    public function letterOfAward()
    {
        return $this->belongsTo(LetterOfAwardTemplate::class, 'template_id');
    }

    public static function getRootClauses(LetterOfAwardTemplate $letterOfAward)
    {
        return self::where('template_id', $letterOfAward->id)
                        ->whereNull('parent_id')
                        ->orderBy('sequence_number', 'asc')
                        ->get();
    }

    public static function getChildrenOf(LetterOfAwardTemplateClause $clause)
    {
        return self::where('parent_id', $clause->id)
                        ->orderBy('sequence_number', 'asc')
                        ->get();
    }

    public static function getParentOf(LetterOfAwardTemplateClause $clause)
    {
        return self::find($clause->parent_id);
    }
}