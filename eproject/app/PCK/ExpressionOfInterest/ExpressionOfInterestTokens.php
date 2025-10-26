<?php namespace PCK\ExpressionOfInterest;

use Illuminate\Database\Eloquent\Model;
use PCK\Companies\Company;
use PCK\Users\User;
use PCK\Tenders\Tender;

class ExpressionOfInterestTokens extends Model {

	protected $table = 'expression_of_interest_tokens';

	protected static function boot()
    {
        parent::boot();
	}
	
	public function tenderstageable()
	{
		return $this->morphTo();
	}
}