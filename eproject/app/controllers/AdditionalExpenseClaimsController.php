<?php

use PCK\Forms\AEClaimForm;
use PCK\AdditionalExpenses\AdditionalExpenseRepository;
use PCK\AdditionalExpenseClaims\AdditionalExpenseClaimRepository;

class AdditionalExpenseClaimsController extends \BaseController {

	private $aeRepo;

	private $aeClaimRepo;

	private $aeClaimForm;

	public function __construct(
		AdditionalExpenseRepository $aeRepo,
		AdditionalExpenseClaimRepository $aeClaimRepo,
		AEClaimForm $aeClaimForm
	)
	{
		$this->aeRepo      = $aeRepo;
		$this->aeClaimRepo = $aeClaimRepo;
		$this->aeClaimForm = $aeClaimForm;
	}

	/**
	 * Show the form for creating a new Additional Expense Claim.
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

		return View::make('additional_expense_claims.create', compact('user', 'ae', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Additional Expense Claim in storage.
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

		$this->aeClaimForm->validate($inputs);

		$this->aeClaimRepo->add($user, $ae, $inputs);

		Flash::success('Successfully added Final Claim!');

		return Redirect::route('ae.show', array( $ae->project_id, $ae->id ));
	}

}