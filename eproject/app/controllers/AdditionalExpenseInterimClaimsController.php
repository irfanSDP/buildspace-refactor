<?php

use PCK\InterimClaims\InterimClaimRepository;
use PCK\Forms\AdditionalExpenseInterimClaimForm;
use PCK\AdditionalExpenses\AdditionalExpenseRepository;
use PCK\AdditionalExpenseInterimClaims\AdditionalExpenseInterimClaimRepository;

class AdditionalExpenseInterimClaimsController extends \BaseController {

	private $aeRepo;

	private $icRepo;

	private $aeInterimClaimRepo;

	private $form;

	public function __construct(
		AdditionalExpenseRepository $aeRepo,
		InterimClaimRepository $icRepo,
		AdditionalExpenseInterimClaimRepository $aeInterimClaimRepo,
		AdditionalExpenseInterimClaimForm $form
	)
	{
		$this->aeRepo             = $aeRepo;
		$this->icRepo             = $icRepo;
		$this->aeInterimClaimRepo = $aeInterimClaimRepo;
		$this->form               = $form;
	}

	/**
	 * Show the form for creating a new Additional Expense Interim Claim.
	 *
	 * @param $project
	 * @param $aeId
	 * @return Response
	 */
	public function create($project, $aeId)
	{
		$ae            = $this->aeRepo->find($project, $aeId);
		$ics           = $this->icRepo->getDropDownListing($project);
		$uploadedFiles = $this->getAttachmentDetails();

		return View::make('additional_expense_interim_claims.create', compact('ae', 'ics', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Additional Expense Interim Claim in storage.
	 *
	 * @param $project
	 * @param $aeId
	 * @return Response
	 */
	public function store($project, $aeId)
	{
		$inputs = Input::all();
		$user   = Confide::user();
		$ae     = $this->aeRepo->find($project, $aeId);

		$this->form->validate($inputs);

		$this->aeInterimClaimRepo->add($ae, $user, $inputs);

		Flash::success('Successfully added Interim Claim!');

		return Redirect::route('ae.show', array( $project->id, $aeId ));
	}

}