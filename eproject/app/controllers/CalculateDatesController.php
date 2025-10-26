<?php

use PCK\Base\DateCalculator;
use PCK\Calendars\CalendarRepository;

class CalculateDatesController extends \BaseController {

	private $dateCalculator;

	private $calendarRepo;

	public function __construct(
		DateCalculator $dateCalculator,
		CalendarRepository $calendarRepo
	)
	{
		$this->dateCalculator = $dateCalculator;
		$this->calendarRepo   = $calendarRepo;
	}

	public function calculates($project)
	{
		$input = Input::all();

		$new_deadline = $this->calendarRepo->calculateFinalDate($project, $input['date'], $input['deadlineDays']);

		return Response::json(compact('new_deadline'));
	}

}