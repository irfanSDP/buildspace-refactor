<?php namespace PCK\WeatherRecordReports;

use PCK\Base\TimestampFormatterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class WeatherRecordReport extends Model implements WeatherStatusType {

	use SoftDeletingTrait, TimestampFormatterTrait, WeatherStatusTypeTrait;

	public function weatherRecord()
	{
		return $this->belongsTo('PCK\WeatherRecords\WeatherRecord');
	}

	public function createdBy()
	{
		return $this->belongsTo('PCK\Users\User', 'created_by');
	}

}