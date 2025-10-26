<?php

use PCK\Clauses\ClauseRepository;
use PCK\Forms\ArchitectInstructionMessageForm;
use PCK\ArchitectInstructions\ArchitectInstructionRepository;
use PCK\ArchitectInstructionMessages\ArchitectInstructionMessageRepository;

class ArchitectInstructionMessagesController extends \BaseController {

	private $aiRepo;

	private $aimRepo;

	private $clauseRepository;

	private $aimForm;

	public function __construct(
		ArchitectInstructionRepository $aiRepo,
		ArchitectInstructionMessageRepository $aimRepo,
		ClauseRepository $clauseRepository,
		ArchitectInstructionMessageForm $aimForm
	)
	{
		$this->aiRepo           = $aiRepo;
		$this->aimRepo          = $aimRepo;
		$this->clauseRepository = $clauseRepository;
		$this->aimForm          = $aimForm;
	}

	/**
	 * Show the form for creating a new Architect Instruction Message.
	 *
	 * @param $project
	 * @param $aiId
	 * @return Response
	 */
	public function create($project, $aiId)
	{
		$user          = Confide::user();
		$ai            = $this->aiRepo->find($project, $aiId);
		$clause        = $this->clauseRepository->findItemsWithClauseById(1);
		$uploadedFiles = $this->getAttachmentDetails();

		return View::make('architect_instruction_messages.create', compact('user', 'ai', 'clause', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Architect Instruction Message in storage.
	 *
	 * @param $project
	 * @param $aiId
	 * @throws \Laracasts\Validation\FormValidationException
	 * @return Response
	 */
	public function store($project, $aiId)
	{
		$ai     = $this->aiRepo->find($project, $aiId);
		$inputs = Input::all();

		$this->aimForm->validate($inputs);

		$this->aimRepo->add($ai, $inputs);

		Flash::success('Successfully replied Step One Message!');

		return Redirect::route('ai.show', array( $project->id, $aiId ));
	}

}