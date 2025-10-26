<?php namespace PCK\Base;

use Carbon\Carbon;

trait TimestampFormatterTrait {

	public function getCreatedAtAttribute()
	{
		return $this->formatTimeStampAttribute('created_at');
	}

	public function getUpdatedAtAttribute()
	{
		return $this->formatTimeStampAttribute('updated_at');
	}

	private function formatTimeStampAttribute($column)
	{
		return Carbon::parse($this->attributes[$column])->format(\Config::get('dates.created_and_updated_at_formatting'));
	}

}