<?php

use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\LOEMessageThirdLevelContractorForm;
use PCK\Forms\LOEMessageThirdLevelArchitectQsForm;
use PCK\LossOrAndExpenses\LossOrAndExpenseRepository;
use PCK\LossOrAndExpenseThirdLevelMessages\LossOrAndExpenseThirdLevelMessageRepository;

class LossAndOrExpenseThirdLevelMessagesController extends \BaseController {

	private $loaRepo;

	private $loaThirdLevelMessageRepo;

	private $calendarRepository;

	private $loeMessageThirdLevelArchitectQsForm;

	private $loeMessageThirdLevelContractorForm;

	public function __construct(
		LossOrAndExpenseRepository $loaRepo,
		LossOrAndExpenseThirdLevelMessageRepository $loaThirdLevelMessageRepo,
		CalendarRepository $calendarRepository,
		LOEMessageThirdLevelArchitectQsForm $loeMessageThirdLevelArchitectQsForm,
		LOEMessageThirdLevelContractorForm $loeMessageThirdLevelContractorForm
	)
	{
		$this->loaRepo                             = $loaRepo;
		$this->loaThirdLevelMessageRepo            = $loaThirdLevelMessageRepo;
		$this->calendarRepository                  = $calendarRepository;
		$this->loeMessageThirdLevelArchitectQsForm = $loeMessageThirdLevelArchitectQsForm;
		$this->loeMessageThirdLevelContractorForm  = $loeMessageThirdLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Lose And/Or Expense Third Level Message.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function create($project, $loeId)
	{
		$user          = Confide::user();
		$loe           = $this->loaRepo->find($project, $loeId);
		$events        = $this->calendarRepository->getEventsListing($loe->project);
		$uploadedFiles = $this->getAttachmentDetails();

		JavaScript::put(compact('events'));

		return View::make('loss_and_or_expense_third_level_messages.create', compact('user', 'loe', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Lose And/Or Expense Third Level Message in storage.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function store($project, $loeId)
	{
		$user = Confide::user();
		$loe  = $this->loaRepo->find($project, $loeId);

		$inputs = Input::all();

        $inputs['deadline_to_comply_with'] = $project->getAppTimeZoneTime($inputs['deadline_to_comply_with'] ?? null);

		if ( $user->hasCompanyProjectRole($project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
		{
			$this->loeMessageThirdLevelArchitectQsForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->loeMessageThirdLevelContractorForm->validate($inputs);
		}

		$this->loaThirdLevelMessageRepo->add($user, $loe, $inputs);

		Flash::success('Successfully replied Step Three Message!');

		return Redirect::route('loe.show', array( $loe->project_id, $loe->id ));
	}

}