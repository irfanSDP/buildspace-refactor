<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model {

	protected $connection = 'buildspace';

	protected $table = 'bs_currency';

	public $timestamps = false;

	public static function getDefault()
	{
		$defaultCountry = Region::getDefault();

		$default = self::where('currency_code', '=', $defaultCountry->currency_code)->first();

		if(!$default)
		{
			$default = self::where('currency_name', '=', $defaultCountry->currency_name)->first();
		}

		if(!$default)
		{
			$default = self::all()->first();
		}

		return $default;
	}
}