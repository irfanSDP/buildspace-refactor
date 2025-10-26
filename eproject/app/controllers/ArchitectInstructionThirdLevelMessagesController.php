<?php

use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\AIMessageThirdLevelArchitectForm;
use PCK\Forms\AIMessageThirdLevelContractorForm;
use PCK\ArchitectInstructions\ArchitectInstructionRepository;
use PCK\ArchitectInstructionThirdLevelMessages\ArchitectInstructionThirdLevelMessageRepository;

class ArchitectInstructionThirdLevelMessagesController extends \BaseController {

	private $aiRepo;

	private $aimThirdLevelRepo;

	private $calendarRepository;

	private $aimThirdLevelArchitectForm;

	private $aimThirdLevelContractorForm;

	public function __construct(
		ArchitectInstructionRepository $aiRepo,
		ArchitectInstructionThirdLevelMessageRepository $aimThirdLevelRepo,
		CalendarRepository $calendarRepository,
		AIMessageThirdLevelArchitectForm $aimThirdLevelArchitectForm,
		AIMessageThirdLevelContractorForm $aimThirdLevelContractorForm
	)
	{
		$this->aiRepo                      = $aiRepo;
		$this->aimThirdLevelRepo           = $aimThirdLevelRepo;
		$this->calendarRepository          = $calendarRepository;
		$this->aimThirdLevelArchitectForm  = $aimThirdLevelArchitectForm;
		$this->aimThirdLevelContractorForm = $aimThirdLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Architect Instruction Third Level Message.
	 *
	 * @param $project
	 * @param $aiId
	 * @return Response
	 */
	public function create($project, $aiId)
	{
		$user          = Confide::user();
		$ai            = $this->aiRepo->find($project, $aiId);
		$events        = $this->calendarRepository->getEventsListing($ai->project);
		$uploadedFiles = $this->getAttachmentDetails();

		JavaScript::put(compact('events'));

		return View::make('architect_instruction_third_level_messages.create', compact('user', 'ai', 'uploadedFiles'));
	}

	/**
	 * Store a newly created resource in Architect Instruction Third Level Message.
	 *
	 * @param $project
	 * @param $aiId
	 * @return Response
	 */
	public function store($project, $aiId)
	{
		$user = Confide::user();
		$ai   = $this->aiRepo->find($project, $aiId);

		$inputs = Input::all();

        $inputs['compliance_date'] = $project->getAppTimeZoneTime($inputs['compliance_date'] ?? null);

		if ( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
		{
			$this->aimThirdLevelArchitectForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->aimThirdLevelContractorForm->validate($inputs);
		}

		$this->aimThirdLevelRepo->add($user, $ai, $inputs);

		Flash::success('Successfully replied Step Three Message!');

		return Redirect::route('ai.show', array( $ai->project_id, $ai->id ));
	}

}