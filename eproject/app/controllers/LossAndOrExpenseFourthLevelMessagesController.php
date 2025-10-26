<?php

use PCK\ContractGroups\Types\Role;
use PCK\Forms\LOEMessageFourthLevelContractorForm;
use PCK\Forms\LOEMessageFourthLevelArchitectQsForm;
use PCK\LossOrAndExpenses\LossOrAndExpenseRepository;
use PCK\LossOrAndExpenseFourthLevelMessages\LossOrAndExpenseFourthLevelMessageRepository;

class LossAndOrExpenseFourthLevelMessagesController extends \BaseController {

	private $loeRepo;

	private $loeFourthLevelMessageRepo;

	private $loeMessageFourthLevelArchitectQsForm;

	private $loeMessageFourthLevelContractorForm;

	public function __construct(
		LossOrAndExpenseRepository $loeRepo,
		LossOrAndExpenseFourthLevelMessageRepository $loeFourthLevelMessageRepo,
		LOEMessageFourthLevelArchitectQsForm $loeMessageFourthLevelArchitectQsForm,
		LOEMessageFourthLevelContractorForm $loeMessageFourthLevelContractorForm
	)
	{
		$this->loeRepo                              = $loeRepo;
		$this->loeFourthLevelMessageRepo            = $loeFourthLevelMessageRepo;
		$this->loeMessageFourthLevelArchitectQsForm = $loeMessageFourthLevelArchitectQsForm;
		$this->loeMessageFourthLevelContractorForm  = $loeMessageFourthLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Loss And/Or Expense Fourth Level Message.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function create($project, $loeId)
	{
		$user                    = Confide::user();
		$loe                     = $this->loeRepo->find($project, $loeId);
		$loeLastArchitectMessage = $this->loeFourthLevelMessageRepo->checkLatestMessageByArchitect($loeId);
		$uploadedFiles           = $this->getAttachmentDetails();

		return View::make('loss_and_or_expense_fourth_level_messages.create', compact('user', 'loe', 'loeLastArchitectMessage', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Loss And/Or Expense Fourth Level Message in storage.
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

		if ( $user->hasCompanyProjectRole($project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
		{
			$this->loeMessageFourthLevelArchitectQsForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->loeMessageFourthLevelContractorForm->validate($inputs);
		}

		$this->loeFourthLevelMessageRepo->add($user, $loe, $inputs);

		Flash::success('Successfully replied Step Four Message!');

		return Redirect::route('loe.show', array( $loe->project_id, $loe->id ));
	}

}