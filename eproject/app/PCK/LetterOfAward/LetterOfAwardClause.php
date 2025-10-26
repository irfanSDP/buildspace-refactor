<?php namespace PCK\LetterOfAward;

use Illuminate\Database\Eloquent\Model;

class LetterOfAwardClause extends Model {
    
    protected $table = 'letter_of_award_clauses';

    public function letter_of_award()
    {
        return $this->belongsTo('PCK\LetterOfAward\LetterOfAward');
    }

    public function comments()
    {
        return $this->hasMany('PCK\LetterOfAward\LetterOfAwardClauseComment', 'clause_id');
    }

    public static function getRootClauses($letterOfAward) {
        return self::where('letter_of_award_id', $letterOfAward->id)
                        ->whereNull('parent_id')
                        ->orderBy('sequence_number', 'asc')
                        ->get();
    }

    public static function getChildrenOf($clause) {
        return self::where('parent_id', $clause->id)
                        ->orderBy('sequence_number', 'asc')
                        ->get();
    }

    public static function getParentOf($clause) {
        return self::find($clause->parent_id);
    }
}

