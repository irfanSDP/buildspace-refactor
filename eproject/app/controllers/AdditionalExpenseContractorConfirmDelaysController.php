<?php

use PCK\Calendars\CalendarRepository;
use PCK\Forms\AEContractorConfirmDelayForm;
use PCK\AdditionalExpenses\AdditionalExpenseRepository;
use PCK\AdditionalExpenseContractorConfirmDelays\AdditionalExpenseContractorConfirmDelayRepository;

class AdditionalExpenseContractorConfirmDelaysController extends \BaseController {

	private $aeRepo;

	private $aeContractorConfirmDelayRepository;

	private $calendarRepository;

	private $aeContractorConfirmDelayForm;

	public function __construct(
		AdditionalExpenseRepository $aeRepo,
		AdditionalExpenseContractorConfirmDelayRepository $aeContractorConfirmDelayRepository,
		CalendarRepository $calendarRepository,
		AEContractorConfirmDelayForm $aeContractorConfirmDelayForm
	)
	{
		$this->aeRepo                             = $aeRepo;
		$this->aeContractorConfirmDelayRepository = $aeContractorConfirmDelayRepository;
		$this->calendarRepository                 = $calendarRepository;
		$this->aeContractorConfirmDelayForm       = $aeContractorConfirmDelayForm;
	}

	/**
	 * Show the form for creating a new Additional Expense Contractor Confirm Delay.
	 *
	 * @param $project
	 * @param $aeId
	 * @return Response
	 */
	public function create($project, $aeId)
	{
		$user          = Confide::user();
		$ae            = $this->aeRepo->find($project, $aeId);
		$uploadedFiles = $this->getAttachmentDetails();
		$calendarRepo  = $this->calendarRepository;
		$events        = $calendarRepo->getEventsListing($ae->project);

		$datesCalculateURL = route('dates.calculateDates', [ $project->id ]);

		JavaScript::put(compact('events', 'datesCalculateURL'));

		return View::make('additional_expense_contractor_confirm_delays.create', compact('user', 'ae', 'uploadedFiles', 'calendarRepo'));
	}

	/**
	 * Store a newly created Additional Expense Contractor Confirm Delay in storage.
	 *
	 * @param $project
	 * @param $aeId
	 * @return Response
	 */
	public function store($project, $aeId)
	{
		$user = Confide::user();
		$ae   = $this->aeRepo->find($project, $aeId);

		$inputs = Input::all();

        $inputs['date_on_which_delay_is_over'] = $project->getAppTimeZoneTime($inputs['date_on_which_delay_is_over'] ?? null);

		$this->aeContractorConfirmDelayForm->validate($inputs);

		$this->aeContractorConfirmDelayRepository->add($user, $ae, $inputs);

		Flash::success('Successfully added Contractor Confirm Delay is Over!');

		return Redirect::route('ae.show', array( $ae->project_id, $ae->id ));
	}

}