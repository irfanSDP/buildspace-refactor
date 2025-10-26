<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class Region extends Model {

	protected $connection = 'buildspace';

	protected $table = 'bs_regions';

	public $timestamps = false;

	public static function getDefault()
	{
		$default = self::where('country', '=', getenv('DEFAULT_COUNTRY'))->first();

		if(!$default)
		{
			$default = self::all()->first();
		}

		return $default;
	}

}