<?php namespace PCK\CalendarSettings;

use Illuminate\Database\Eloquent\Model;

class CalendarSetting extends Model {

	public function country()
	{
		return $this->belongsTo('PCK\Countries\Country');
	}

}