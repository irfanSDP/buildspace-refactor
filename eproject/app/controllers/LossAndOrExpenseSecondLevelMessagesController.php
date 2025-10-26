<?php

use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\LOEMessageSecondLevelArchitectForm;
use PCK\Forms\LOEMessageSecondLevelContractorForm;
use PCK\LossOrAndExpenses\LossOrAndExpenseRepository;
use PCK\LossOrAndExpenseSecondLevelMessages\LossOrAndExpenseSecondLevelMessageRepository;

class LossAndOrExpenseSecondLevelMessagesController extends \BaseController {

	private $loeRepo;

	private $loeSecondLevelMessageRepo;

	private $calendarRepository;

	private $loeSecondLevelArchitectForm;

	private $loeSecondLevelContractorForm;

	public function __construct(
		LossOrAndExpenseRepository $loeRepo,
		LossOrAndExpenseSecondLevelMessageRepository $loeSecondLevelMessageRepo,
		CalendarRepository $calendarRepository,
		LOEMessageSecondLevelArchitectForm $loeSecondLevelArchitectForm,
		LOEMessageSecondLevelContractorForm $loeSecondLevelContractorForm
	)
	{
		$this->loeRepo                      = $loeRepo;
		$this->loeSecondLevelMessageRepo    = $loeSecondLevelMessageRepo;
		$this->calendarRepository           = $calendarRepository;
		$this->loeSecondLevelArchitectForm  = $loeSecondLevelArchitectForm;
		$this->loeSecondLevelContractorForm = $loeSecondLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Loss And/Or Expense Second Level Message.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function create($project, $loeId)
	{
		$user           = Confide::user();
		$loe            = $this->loeRepo->find($project, $loeId);
		$loeLastMessage = $this->loeSecondLevelMessageRepo->checkLatestMessagePosterRole($loe->id);
		$events         = $this->calendarRepository->getEventsListing($loe->project);
		$uploadedFiles  = $this->getAttachmentDetails();

		JavaScript::put(compact('events'));

		return View::make('loss_and_or_expense_second_level_messages.create', compact('user', 'loe', 'loeLastMessage', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Loss And/Or Expense Second Level Message in storage.
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

        $inputs['requested_new_deadline']   = $project->getAppTimeZoneTime($inputs['requested_new_deadline'] ?? null);
        $inputs['grant_different_deadline'] = $project->getAppTimeZoneTime($inputs['grant_different_deadline'] ?? null);

		if ( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
		{
			$this->loeSecondLevelArchitectForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->loeSecondLevelContractorForm->validate($inputs);
		}

		$this->loeSecondLevelMessageRepo->add($user, $loe, $inputs);

		Flash::success('Successfully replied Step Second Message!');

		return Redirect::route('loe.show', array( $loe->project_id, $loe->id ));
	}

}