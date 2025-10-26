<?php

use PCK\ContractGroups\Types\Role;
use PCK\Forms\AEMessageFirstLevelArchitectForm;
use PCK\Forms\AEMessageFirstLevelContractorForm;
use PCK\AdditionalExpenses\AdditionalExpenseRepository;
use PCK\AdditionalExpenseFirstLevelMessages\AdditionalExpenseFirstLevelMessageRepository;

class AdditionalExpenseFirstLevelMessagesController extends \BaseController {

	private $aeRepo;

	private $aeFirstLevelMessageRepo;

	private $aeFirstLevelArchitectForm;

	private $aeFirstLevelContractorForm;

	public function __construct(
		AdditionalExpenseRepository $aeRepo,
		AdditionalExpenseFirstLevelMessageRepository $aeFirstLevelMessageRepo,
		AEMessageFirstLevelArchitectForm $aeFirstLevelArchitectForm,
		AEMessageFirstLevelContractorForm $aeFirstLevelContractorForm
	)
	{
		$this->aeRepo                     = $aeRepo;
		$this->aeFirstLevelMessageRepo    = $aeFirstLevelMessageRepo;
		$this->aeFirstLevelArchitectForm  = $aeFirstLevelArchitectForm;
		$this->aeFirstLevelContractorForm = $aeFirstLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Additional Expense First Level Message.
	 *
	 * @param $project
	 * @param $aeId
	 * @return Response
	 */
	public function create($project, $aeId)
	{
		$user          = \Confide::user();
		$ae            = $this->aeRepo->find($project, $aeId);
		$uploadedFiles = $this->getAttachmentDetails();

		return View::make('additional_expense_first_level_messages.create', compact('user', 'ae', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Additional Expense First Level Message in storage.
	 *
	 * @param $project
	 * @param $aeId
	 * @return Response
	 */
	public function store($project, $aeId)
	{
		$user = \Confide::user();
		$ae   = $this->aeRepo->find($project, $aeId);

		$inputs = Input::all();

		if ( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
		{
			$this->aeFirstLevelArchitectForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->aeFirstLevelContractorForm->validate($inputs);
		}

		$this->aeFirstLevelMessageRepo->add($user, $ae, $inputs);

		Flash::success('Successfully replied Step One Message!');

		return Redirect::route('ae.show', array( $ae->project_id, $ae->id ));
	}

}