<?php

use PCK\ContractGroups\Types\Role;
use PCK\Forms\LOEMessageFirstLevelArchitectForm;
use PCK\Forms\LOEMessageFirstLevelContractorForm;
use PCK\LossOrAndExpenses\LossOrAndExpenseRepository;
use PCK\LossOrAndExpenseFirstLevelMessages\LossOrAndExpenseFirstLevelMessageRepository;

class LossAndOrExpenseFirstLevelMessagesController extends \BaseController {

	private $loeRepo;

	private $loeFirstLevelMessageRepo;

	private $loeFirstLevelArchitectForm;

	private $loeFirstLevelContractorForm;

	public function __construct(
		LossOrAndExpenseRepository $loeRepo,
		LossOrAndExpenseFirstLevelMessageRepository $loeFirstLevelMessageRepo,
		LOEMessageFirstLevelArchitectForm $loeFirstLevelArchitectForm,
		LOEMessageFirstLevelContractorForm $loeFirstLevelContractorForm
	)
	{
		$this->loeRepo                     = $loeRepo;
		$this->loeFirstLevelMessageRepo    = $loeFirstLevelMessageRepo;
		$this->loeFirstLevelArchitectForm  = $loeFirstLevelArchitectForm;
		$this->loeFirstLevelContractorForm = $loeFirstLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Loss And/Or Expense First Level Message.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function create($project, $loeId)
	{
		$user          = \Confide::user();
		$loe           = $this->loeRepo->find($project, $loeId);
		$uploadedFiles = $this->getAttachmentDetails();

		return View::make('loss_and_or_expense_first_level_messages.create', compact('user', 'loe', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Loss And/Or Expense First Level Message in storage.
	 *
	 * @param $project
	 * @param $loeID
	 * @return Response
	 * @throws \Laracasts\Validation\FormValidationException
	 */
	public function store($project, $loeID)
	{
		$user = Confide::user();
		$loe  = $this->loeRepo->find($project, $loeID);

		$inputs = Input::all();

		if ( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
		{
			$this->loeFirstLevelArchitectForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->loeFirstLevelContractorForm->validate($inputs);
		}

		$this->loeFirstLevelMessageRepo->add($user, $loe, $inputs);

		Flash::success('Successfully replied Step One Message!');

		return Redirect::route('loe.show', array( $loe->project_id, $loe->id ));
	}

}