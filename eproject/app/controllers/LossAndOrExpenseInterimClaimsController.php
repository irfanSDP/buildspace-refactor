<?php

use PCK\InterimClaims\InterimClaimRepository;
use PCK\Forms\LossAndOrExpenseInterimClaimForm;
use PCK\LossOrAndExpenses\LossOrAndExpenseRepository;
use PCK\LossOrAndExpenseInterimClaims\LossOrAndExpenseInterimClaimRepository;

class LossAndOrExpenseInterimClaimsController extends \BaseController {

	private $loeRepo;

	private $icRepo;

	private $loeInterimClaimRepo;

	private $form;

	public function __construct(
		LossOrAndExpenseRepository $loeRepo,
		InterimClaimRepository $icRepo,
		LossOrAndExpenseInterimClaimRepository $loeInterimClaimRepo,
		LossAndOrExpenseInterimClaimForm $form
	)
	{
		$this->loeRepo             = $loeRepo;
		$this->icRepo              = $icRepo;
		$this->loeInterimClaimRepo = $loeInterimClaimRepo;
		$this->form                = $form;
	}

	/**
	 * Show the form for creating a new Loss And/Or Expense Interim Claim.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function create($project, $loeId)
	{
		$loe           = $this->loeRepo->find($project, $loeId);
		$ics           = $this->icRepo->getDropDownListing($project);
		$uploadedFiles = $this->getAttachmentDetails();

		return View::make('loss_and_or_expense_interim_claims.create', compact('loe', 'ics', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Loss And/Or Expense Interim Claim in storage.
	 *
	 * @param $project
	 * @param $loeId
	 * @return Response
	 */
	public function store($project, $loeId)
	{
		$inputs = Input::all();
		$user   = Confide::user();
		$loe    = $this->loeRepo->find($project, $loeId);

		$this->form->validate($inputs);

		$this->loeInterimClaimRepo->add($loe, $user, $inputs);

		Flash::success('Successfully added Interim Claim!');

		return Redirect::route('loe.show', array( $project->id, $loeId ));
	}

}