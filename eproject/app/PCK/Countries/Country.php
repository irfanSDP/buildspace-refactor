<?php namespace PCK\Countries;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Country extends Model {

	public function states()
	{
		return $this->hasMany('PCK\States\State')
			->orderBy('id', 'desc');
	}

	public function projects()
	{
		return $this->hasMany('PCK\Projects\Project')
			->orderBy('id', 'desc');
	}

	public function events()
	{
		return $this->hasMany('PCK\Calendars\Calendar')
			->orderBy('id', 'desc');
	}

	public function currencySetting()
	{
		return $this->hasOne('PCK\Countries\CurrencySetting');
	}

	// use to fix trailing data issue with Carbon
	public function getDates()
	{
		return array();
	}

	public static function getRecordsByIds(Array $ids)
    {
		if(count($ids) == 0) return [];

        $query = "SELECT id, country
                    FROM countries 
                    WHERE id IN (" . implode(', ', $ids) . ")
                    ORDER BY id ASC;";

        $queryResult = DB::select(DB::raw($query));

		return array_map(function($record) {
			return trim($record);
		}, array_column($queryResult, 'country'));
    }
}