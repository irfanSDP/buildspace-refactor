<?php namespace PCK\States;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use PCK\Traits\TimeAccessorTrait;

class State extends Model {

    use TimeAccessorTrait;

    public function country()
    {
        return $this->belongsTo('PCK\Countries\Country');
    }

    public function events()
    {
        return $this->hasMany('PCK\Calendars\Calendar')->orderBy('id', 'desc');
    }

    public static function getRecordsByIds(Array $ids)
    {
        if(count($ids) == 0) return [];

        $query = "SELECT id, name
                    FROM states 
                    WHERE id IN (" . implode(', ', $ids) . ")
                    ORDER BY id ASC;";

        $queryResult = DB::select(DB::raw($query));

        return array_map(function($record) {
			return trim($record);
		}, array_column($queryResult, 'name'));
    }
}