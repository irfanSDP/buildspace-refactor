<?php

use PCK\Calendars\CalendarRepository;
use PCK\Forms\EOTContractorConfirmDelayForm;
use PCK\ExtensionOfTimes\ExtensionOfTimeRepository;
use PCK\ExtensionOfTimeContractorConfirmDelays\ExtensionOfTimeContractorConfirmDelayRepo;

class ExtensionOfTimeContractorConfirmDelaysController extends \BaseController {

	private $eotRepo;

	private $eotCCDRepo;

	private $calendarRepository;

	private $eotContractorDelayForm;

	public function __construct(
		ExtensionOfTimeRepository $eotRepo,
		ExtensionOfTimeContractorConfirmDelayRepo $eotCCDRepo,
		CalendarRepository $calendarRepository,
		EOTContractorConfirmDelayForm $eotContractorDelayForm
	)
	{
		$this->eotRepo                = $eotRepo;
		$this->eotCCDRepo             = $eotCCDRepo;
		$this->calendarRepository     = $calendarRepository;
		$this->eotContractorDelayForm = $eotContractorDelayForm;
	}

	/**
	 * Show the form for creating a new Extension Of Time Contract Confirm Delay.
	 *
	 * @param $project
	 * @param $eotId
	 * @return Response
	 */
	public function create($project, $eotId)
	{
		$user          = Confide::user();
		$eot           = $this->eotRepo->find($project, $eotId);
		$calendarRepo  = $this->calendarRepository;
		$events        = $calendarRepo->getEventsListing($eot->project);
		$uploadedFiles = $this->getAttachmentDetails();

		$datesCalculateURL = route('dates.calculateDates', [ $project->id ]);

		JavaScript::put(compact('events', 'datesCalculateURL'));

		return View::make('extension_of_times_contractor_confirm_delays.create', compact('user', 'eot', 'uploadedFiles', 'calendarRepo'));
	}

	/**
	 * Store a newly created Extension Of Time Contract Confirm Delay in storage.
	 *
	 * @param $project
	 * @param $eotId
	 * @return Response
	 */
	public function store($project, $eotId)
	{
		$user = Confide::user();
		$eot  = $this->eotRepo->find($project, $eotId);

		$inputs = Input::all();

        $inputs['date_on_which_delay_is_over']        = $project->getAppTimeZoneTime($inputs['date_on_which_delay_is_over'] ?? null);
        $inputs['deadline_to_submit_final_eot_claim'] = $project->getAppTimeZoneTime($inputs['deadline_to_submit_final_eot_claim'] ?? null);

		$this->eotContractorDelayForm->validate($inputs);

		$this->eotCCDRepo->add($user, $eot, $inputs);

		Flash::success('Successfully added Contractor Confirm Delay is Over!');

		return Redirect::route('eot.show', array( $eot->project_id, $eot->id ));
	}

}