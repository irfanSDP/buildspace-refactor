<?php namespace PCK\LetterOfAward;

use Illuminate\Database\Eloquent\Model;

class LetterOfAwardLog extends Model {
    protected $table = 'letter_of_award_logs';

    const CONTRACT_DETAILS = 1;
    const CLAUSES = 2;
    const SIGNATORY = 4;

    public function getLogTypeByIdentifier($identifier) {
        $mapping = [
            self::CONTRACT_DETAILS => trans('letterOfAward.contractDetails'),
            self::CLAUSES          => trans('letterOfAward.clauses'),
            self::SIGNATORY        => trans('letterOfAward.signatory'),
        ];

        return $mapping[$identifier];
    }

    public function user()
    {
        return $this->belongsTo('PCK\Users\User');
    }
}

