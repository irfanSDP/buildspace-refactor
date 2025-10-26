<?php namespace PCK\LetterOfAward;

use Illuminate\Database\Eloquent\Model;

class LetterOfAwardPrintSetting extends Model {
    
    protected $table = 'letter_of_award_print_settings';

    public function letterOfAward()
    {
        return $this->belongsTo('PCK\LetterOfAward\LetterOfAward');
    }
}