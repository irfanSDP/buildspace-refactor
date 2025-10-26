<?php

use PCK\InterimClaims\InterimClaimRepository;
use PCK\Forms\ArchitectInstructionInterimClaimForm;
use PCK\ArchitectInstructions\ArchitectInstructionRepository;
use PCK\ArchitectInstructionInterimClaims\ArchitectInstructionInterimClaimRepository;

class ArchitectInstructionInterimClaimsController extends \BaseController {

	private $aiRepo;

	private $icRepo;

	private $aiInterimClaimRepository;

	private $form;

	public function __construct(
		ArchitectInstructionRepository $aiRepo,
		InterimClaimRepository $icRepo,
		ArchitectInstructionInterimClaimRepository $aiInterimClaimRepository,
		ArchitectInstructionInterimClaimForm $form
	)
	{
		$this->aiRepo                   = $aiRepo;
		$this->icRepo                   = $icRepo;
		$this->aiInterimClaimRepository = $aiInterimClaimRepository;
		$this->form                     = $form;
	}

	/**
	 * Show the form for creating a new Architect Instruction Interim Claims.
	 *
	 * @param $project
	 * @param $aiId
	 * @return Response
	 */
	public function create($project, $aiId)
	{
		$ai  = $this->aiRepo->find($project, $aiId);
		$ics = $this->icRepo->getDropDownListing($project);

		return View::make('architect_instruction_interim_claims.create', compact('ai', 'ics'));
	}

	/**
	 * Store a newly created Architect Instruction Interim Claims in storage.
	 *
	 * @param $project
	 * @param $aiId
	 * @return Response
	 */
	public function store($project, $aiId)
	{
		$inputs = Input::all();
		$user   = Confide::user();
		$ai     = $this->aiRepo->find($project, $aiId);

		$this->form->validate($inputs);

		$this->aiInterimClaimRepository->add($ai, $user, $inputs);

		Flash::success('Successfully added Interim Claim!');

		return Redirect::route('ai.show', array( $project->id, $aiId ));
	}

}