<?php
namespace PCK\ConsultantManagement;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PCK\Users\User;
use PCK\Helpers\StringOperations;

use PCK\ConsultantManagement\LetterOfAward;

class LetterOfAwardClause extends Model
{
    protected $table = 'consultant_management_letter_of_award_clauses';

    public function letterOfAward()
    {
        return $this->belongsTo(LetterOfAward::class, 'template_id');
    }

    public static function getRootClauses(LetterOfAward $letterOfAward)
    {
        return self::where('template_id', $letterOfAward->id)
                        ->whereNull('parent_id')
                        ->orderBy('sequence_number', 'asc')
                        ->get();
    }

    public static function getChildrenOf(LetterOfAwardClause $clause)
    {
        return self::where('parent_id', $clause->id)
                        ->orderBy('sequence_number', 'asc')
                        ->get();
    }

    public static function getParentOf(LetterOfAwardClause $clause)
    {
        return self::find($clause->parent_id);
    }
}