<?php

use PCK\WeatherRecords\WeatherRecord;
use PCK\WeatherRecords\WeatherRecordRepository;
use PCK\WeatherRecordReports\WeatherRecordReportRepository;

class WeatherRecordReportsController extends \BaseController {

	private $wrRepo;

	private $wrrRepo;

	public function __construct(
		WeatherRecordRepository $wrRepo,
		WeatherRecordReportRepository $wrrRepo
	)
	{
		$this->wrRepo  = $wrRepo;
		$this->wrrRepo = $wrrRepo;
	}

	/**
	 * Show the form for creating a new Weather Record Report.
	 *
	 * @param        $project
	 * @param null   $wrId
	 * @param string $mode
	 * @return Response
	 */
	public function create($project, $wrId = null, $mode = 'new')
	{
		return View::make('weather_record_reports.create', compact('project', 'wrId', 'mode'));
	}

	/**
	 * Store a newly created Weather Record Report in storage.
	 *
	 * @param        $project
	 * @param null   $wrId
	 * @param string $mode
	 * @return Response
	 */
	public function store($project, $wrId = null, $mode = 'new')
	{
		$user          = \Confide::user();
		$inputs        = Input::all();
		$weatherRecord = $this->wrrRepo->add($this->wrRepo->find($wrId, true), $user, $project, $inputs);

		\Flash::success("New Weather Detail Successfully Added!");

		if ( $mode == 'new' )
		{
			return Redirect::route('wr.create', array( $project->id, $weatherRecord->id ));
		}

		return Redirect::route('wr.show', array( $project->id, $weatherRecord->id ));
	}

	/**
	 * Remove the specified Weather Record Report from storage.
	 *
	 * @param        $project
	 * @param        $wrId
	 * @param        $wrrId
	 * @param string $mode
	 * @return Response
	 */
	public function destroy($project, $wrId, $wrrId, $mode = 'new')
	{
		$weatherReport = $this->wrRepo->find($wrId);

		if ( $weatherReport->status == WeatherRecord::NOT_YET_VERIFY_TEXT or $weatherReport->status == WeatherRecord::VERIFIED_TEXT )
		{
			throw new InvalidArgumentException('Invalid delete Weather Detail due to Weather Record has been published.');
		}

		$this->wrrRepo->delete($this->wrrRepo->find($wrId, $wrrId));

		\Flash::success("Weather Detail Successfully Deleted!");

		if ( $mode == 'new' )
		{
			return Redirect::route('wr.create', array( $project->id, $weatherReport->id ));
		}

		return Redirect::route('wr.show', array( $project->id, $weatherReport->id ));
	}

}