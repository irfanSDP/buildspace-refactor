<?php namespace PCK\WeatherRecordReports;

use PCK\Users\User;
use PCK\Projects\Project;
use PCK\WeatherRecords\WeatherRecord;

class WeatherRecordReportRepository {

	private $weatherRecordReport;

	public function __construct(WeatherRecordReport $weatherRecordReport)
	{
		$this->weatherRecordReport = $weatherRecordReport;
	}

	public function find($wrId, $wrrId)
	{
		return $this->weatherRecordReport
			->where('id', '=', $wrrId)
			->where('weather_record_id', '=', $wrId)
			->firstOrFail();
	}

	public function add(WeatherRecord $weatherRecord, User $user, Project $project, array $inputs)
	{
		if ( !$weatherRecord->exists )
		{
			$weatherRecord->project_id = $project->id;
			$weatherRecord->created_by = $user->id;
			$weatherRecord->date       = 'NOW()';
			$weatherRecord->status     = WeatherRecord::PREPARING;

			$weatherRecord->save();
		}

		$wer                 = $this->weatherRecordReport;
		$wer->created_by     = $user->id;
		$wer->from_time      = $inputs['from_time'];
		$wer->to_time        = $inputs['to_time'];
		$wer->weather_status = $inputs['weather_status'];

		$wer->weatherRecord()->associate($weatherRecord);

		$wer->save();

		return $weatherRecord;
	}

	public function delete(WeatherRecordReport $weatherRecordReport)
	{
		$weatherRecordReport->delete();

		return $weatherRecordReport;
	}

}