<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class PostContractLetterOfAwardRententionSumModule extends Model 
{
    protected $connection = 'buildspace';

    protected $table = 'bs_letter_of_award_retention_sum_modules';

    public function letterOfAward()
    {
        return $this->belongsTo('PCK\Buildspace\PostContractLetterOfAward', 'new_post_contract_form_information_id');
    }

    public static function isIncluded($letterOfAwardId, $type)
    {
        $record = self::where('new_post_contract_form_information_id', $letterOfAwardId)->where('type', $type)->get();

        return ( ! $record->isEmpty() );
    }
}