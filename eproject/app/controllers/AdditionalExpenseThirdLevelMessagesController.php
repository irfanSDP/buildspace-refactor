<?php

use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\AEMessageThirdLevelContractorForm;
use PCK\Forms\AEMessageThirdLevelArchitectQsForm;
use PCK\AdditionalExpenses\AdditionalExpenseRepository;
use PCK\AdditionalExpenseThirdLevelMessages\AdditionalExpenseThirdLevelMessageRepository;

class AdditionalExpenseThirdLevelMessagesController extends \BaseController {

	private $aeRepo;

	private $aeThirdLevelMessageRepository;

	private $calendarRepository;

	private $aeMessageThirdLevelArchitectQsForm;

	private $aeMessageThirdLevelContractorForm;

	public function __construct(
		AdditionalExpenseRepository $aeRepo,
		AdditionalExpenseThirdLevelMessageRepository $aeThirdLevelMessageRepository,
		CalendarRepository $calendarRepository,
		AEMessageThirdLevelArchitectQsForm $aeMessageThirdLevelArchitectQsForm,
		AEMessageThirdLevelContractorForm $aeMessageThirdLevelContractorForm
	)
	{
		$this->aeRepo                             = $aeRepo;
		$this->aeThirdLevelMessageRepository      = $aeThirdLevelMessageRepository;
		$this->calendarRepository                 = $calendarRepository;
		$this->aeMessageThirdLevelArchitectQsForm = $aeMessageThirdLevelArchitectQsForm;
		$this->aeMessageThirdLevelContractorForm  = $aeMessageThirdLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Additional Expense Third Level Message.
	 *
	 * @param $project
	 * @param $aeId
	 * @return Response
	 */
	public function create($project, $aeId)
	{
		$user          = Confide::user();
		$ae            = $this->aeRepo->find($project, $aeId);
		$events        = $this->calendarRepository->getEventsListing($ae->project);
		$uploadedFiles = $this->getAttachmentDetails();

		JavaScript::put(compact('events'));

		return View::make('additional_expense_third_level_messages.create', compact('user', 'ae', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Additional Expense Third Level Message in storage.
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

        $inputs['deadline_to_comply_with'] = $project->getAppTimeZoneTime($inputs['deadline_to_comply_with'] ?? null);

		if ( $user->hasCompanyProjectRole($project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER)) )
		{
			$this->aeMessageThirdLevelArchitectQsForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->aeMessageThirdLevelContractorForm->validate($inputs);
		}

		$this->aeThirdLevelMessageRepository->add($user, $ae, $inputs);

		Flash::success('Successfully replied Step Three Message!');

		return Redirect::route('ae.show', array( $ae->project_id, $ae->id ));
	}

}