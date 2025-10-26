<?php namespace PCK\Forms;

use Carbon\Carbon;
use PCK\Projects\Project;
use PCK\Calendars\CalendarRepository;
use Laracasts\Validation\FormValidator;
use Laracasts\Validation\FactoryInterface as ValidatorFactory;

class AddNewArchitectInstructionForm extends FormValidator {

	private $dateCalculator;

	private $project;

	private $minDaysToComplyFromToday = 0;

	public function __construct(CalendarRepository $dateCalculator, ValidatorFactory $validator)
	{
		$this->dateCalculator = $dateCalculator;

		parent::__construct($validator);
	}

	public function getValidationRules()
	{
		$lastReturnDates = $this->getLastValidDateForSubmission();

		return [
			'reference'          => 'required',
			'instruction'        => 'required',
			'deadline_to_comply' => "date|after:{$lastReturnDates}",
		];
	}

	public function setProject(Project $project)
	{
		$this->project = $project;
	}

	public function setMinDaysToComplyFromToday($minDaysToComplyFromToday)
	{
		$this->minDaysToComplyFromToday = $minDaysToComplyFromToday;
	}

	private function getLastValidDateForSubmission()
	{
		return $this->dateCalculator->calculateFinalDate($this->project, Carbon::now(), $this->minDaysToComplyFromToday);
	}

}