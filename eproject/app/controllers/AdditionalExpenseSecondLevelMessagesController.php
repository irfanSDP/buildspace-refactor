<?php

use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\AEMessageSecondLevelArchitectForm;
use PCK\Forms\AEMessageSecondLevelContractorForm;
use PCK\AdditionalExpenses\AdditionalExpenseRepository;
use PCK\AdditionalExpenseSecondLevelMessages\AdditionalExpenseSecondLevelMessageRepository;

class AdditionalExpenseSecondLevelMessagesController extends \BaseController {

	private $aeRepo;

	private $aeSecondLevelMessageRepository;

	private $calendarRepository;

	private $aeMessageSecondLevelArchitectForm;

	private $aeMessageSecondLevelContractorForm;

	public function __construct(
		AdditionalExpenseRepository $aeRepo,
		AdditionalExpenseSecondLevelMessageRepository $aeSecondLevelMessageRepository,
		CalendarRepository $calendarRepository,
		AEMessageSecondLevelArchitectForm $aeMessageSecondLevelArchitectForm,
		AEMessageSecondLevelContractorForm $aeMessageSecondLevelContractorForm
	)
	{
		$this->aeRepo                             = $aeRepo;
		$this->aeSecondLevelMessageRepository     = $aeSecondLevelMessageRepository;
		$this->calendarRepository                 = $calendarRepository;
		$this->aeMessageSecondLevelArchitectForm  = $aeMessageSecondLevelArchitectForm;
		$this->aeMessageSecondLevelContractorForm = $aeMessageSecondLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Additional Expense Second Level Message.
	 *
	 * @param $project
	 * @param $aiId
	 * @return Response
	 */
	public function create($project, $aiId)
	{
		$user          = Confide::user();
		$ae            = $this->aeRepo->find($project, $aiId);
		$aeLastMessage = $this->aeSecondLevelMessageRepository->checkLatestMessagePosterRole($ae->id);
		$events        = $this->calendarRepository->getEventsListing($ae->project);
		$uploadedFiles = $this->getAttachmentDetails();

		JavaScript::put(compact('events'));

		return View::make('additional_expense_second_level_messages.create', compact('user', 'ae', 'aeLastMessage', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Additional Expense Second Level Message in storage.
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

        $inputs['requested_new_deadline']   = $project->getAppTimeZoneTime($inputs['requested_new_deadline'] ?? null);
        $inputs['grant_different_deadline'] = $project->getAppTimeZoneTime($inputs['grant_different_deadline'] ?? null);

		if ( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
		{
			$this->aeMessageSecondLevelArchitectForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->aeMessageSecondLevelContractorForm->validate($inputs);
		}

		$this->aeSecondLevelMessageRepository->add($user, $ae, $inputs);

		Flash::success('Successfully replied Step Two Message!');

		return Redirect::route('ae.show', array( $ae->project_id, $ae->id ));
	}

}