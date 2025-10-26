<?php

use PCK\Calendars\CalendarRepository;
use PCK\Forms\LOEContractorConfirmDelayForm;
use PCK\LossOrAndExpenses\LossOrAndExpenseRepository;
use PCK\LossOrAndExpenseContractorConfirmDelays\LossOrAndExpenseContractorConfirmDelayRepository;

class LossAndOrExpenseContractorConfirmDelaysController extends \BaseController {

	private $loeRepo;

	private $loeCCDRepo;

	private $loeContractorDelayForm;

	private $calendarRepository;

	public function __construct(
		LossOrAndExpenseRepository $loeRepo,
		LossOrAndExpenseContractorConfirmDelayRepository $loeCCDRepo,
		CalendarRepository $calendarRepository,
		LOEContractorConfirmDelayForm $loeContractorDelayForm
	)
	{
		$this->loeRepo                = $loeRepo;
		$this->loeCCDRepo             = $loeCCDRepo;
		$this->calendarRepository     = $calendarRepository;
		$this->loeContractorDelayForm = $loeContractorDelayForm;
	}

	/**
	 * Show the form for creating a new Lost And/Or Expense Contractor Confirm Delay.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function create($project, $loeId)
	{
		$user          = Confide::user();
		$loe           = $this->loeRepo->find($project, $loeId);
		$calendarRepo  = $this->calendarRepository;
		$events        = $calendarRepo->getEventsListing($loe->project);
		$uploadedFiles = $this->getAttachmentDetails();

		$datesCalculateURL = route('dates.calculateDates', [ $project->id ]);

		JavaScript::put(compact('events', 'datesCalculateURL'));

		return View::make('loss_and_or_expense_contractor_confirm_delays.create', compact('user', 'loe', 'uploadedFiles', 'calendarRepo'));
	}

	/**
	 * Store a newly created Lost And/Or Expense Contractor Confirm Delay in storage.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function store($project, $loeId)
	{
		$user = Confide::user();
		$loe  = $this->loeRepo->find($project, $loeId);

		$inputs = Input::all();

        $inputs['date_on_which_delay_is_over'] = $project->getAppTimeZoneTime($inputs['date_on_which_delay_is_over'] ?? null);

		$this->loeContractorDelayForm->validate($inputs);

		$this->loeCCDRepo->add($user, $loe, $inputs);

		Flash::success('Successfully added Contractor Confirm Delay is Over!');

		return Redirect::route('loe.show', array( $loe->project_id, $loe->id ));
	}

}