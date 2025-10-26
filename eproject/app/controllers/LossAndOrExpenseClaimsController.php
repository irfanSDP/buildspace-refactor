<?php

use PCK\Forms\LOEClaimForm;
use PCK\LossOrAndExpenseClaims\LossOrAndExpenseClaimRepository;
use PCK\LossOrAndExpenses\LossOrAndExpenseRepository;

class LossAndOrExpenseClaimsController extends \BaseController {

	private $loeRepo;

	private $loeClaimForm;

	private $loeClaimRepo;

	public function __construct(
		LossOrAndExpenseRepository $loeRepo,
		LossOrAndExpenseClaimRepository $loeClaimRepo,
		LOEClaimForm $loeClaimForm
	)
	{
		$this->loeRepo      = $loeRepo;
		$this->loeClaimRepo = $loeClaimRepo;
		$this->loeClaimForm = $loeClaimForm;
	}

	/**
	 * Show the form for creating a new Loss And/Or Expense Claim.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function create($project, $loeId)
	{
		$user          = Confide::user();
		$loe           = $this->loeRepo->find($project, $loeId);
		$uploadedFiles = $this->getAttachmentDetails();

		return View::make('loss_and_or_expense_claims.create', compact('user', 'loe', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Loss And/Or Expense Claim in storage.
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

		$this->loeClaimForm->validate($inputs);

		$this->loeClaimRepo->add($user, $loe, $inputs);

		Flash::success('Successfully added Final Claim!');

		return Redirect::route('loe.show', array( $loe->project_id, $loe->id ));
	}

}