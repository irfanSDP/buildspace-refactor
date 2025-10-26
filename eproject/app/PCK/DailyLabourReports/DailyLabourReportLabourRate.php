<?php namespace PCK\DailyLabourReports;

use Illuminate\Database\Eloquent\Model;
use PCK\Projects\Project;

class DailyLabourReportLabourRate extends Model {
	
	protected $table = 'daily_labour_report_labour_rates';

	public function dailyLabourReport()
	{
		return $this->belongsTo('PCK\DailyLabourReports\DailyLabourReport','daily_labour_report_id');
	}

	public static function getLabourRatesRecords ($labourType , $dailyLabourReport)
  	{
        return self::where("daily_labour_report_id", $dailyLabourReport->id)
                                  ->where("labour_type",$labourType)
                                  ->first();
  	}
}