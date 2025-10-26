<?php

use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\EOTMessageThirdLevelArchitectForm;
use PCK\Forms\EOTMessageThirdLevelContractorForm;
use PCK\ExtensionOfTimes\ExtensionOfTimeRepository;
use PCK\ExtensionOfTimeThirdLevelMessages\ExtensionOfTimeThirdLevelMessageRepository;

class ExtensionOfTimeThirdLevelMessagesController extends \BaseController {

	private $eotRepo;

	private $eotThirdLevelMessageRepo;

	private $calendarRepository;

	private $eotThirdLevelArchitectForm;

	private $eotThirdLevelContractorForm;

	public function __construct(
		ExtensionOfTimeRepository $eotRepo,
		ExtensionOfTimeThirdLevelMessageRepository $eotThirdLevelMessageRepo,
		CalendarRepository $calendarRepository,
		EOTMessageThirdLevelArchitectForm $eotThirdLevelArchitectForm,
		EOTMessageThirdLevelContractorForm $eotThirdLevelContractorForm
	)
	{
		$this->eotRepo                     = $eotRepo;
		$this->eotThirdLevelMessageRepo    = $eotThirdLevelMessageRepo;
		$this->calendarRepository          = $calendarRepository;
		$this->eotThirdLevelArchitectForm  = $eotThirdLevelArchitectForm;
		$this->eotThirdLevelContractorForm = $eotThirdLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Extension Of Time Third Level Message.
	 *
	 * @param $project
	 * @param $eotId
	 * @return Response
	 */
	public function create($project, $eotId)
	{
		$user          = Confide::user();
		$eot           = $this->eotRepo->find($project, $eotId);
		$events        = $this->calendarRepository->getEventsListing($eot->project);
		$uploadedFiles = $this->getAttachmentDetails();

		JavaScript::put(compact('events'));

		return View::make('extension_of_time_third_level_messages.create', compact('user', 'eot', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Extension Of Time Third Level Message in storage.
	 *
	 * @param $project
	 * @param $eotId
	 * @return Response
	 */
	public function store($project, $eotId)
	{
		$user = Confide::user();
		$eot  = $this->eotRepo->find($project, $eotId);

		$inputs = Input::all();

        $inputs['deadline_to_comply_with'] = $project->getAppTimeZoneTime($inputs['deadline_to_comply_with'] ?? null);

		if ( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
		{
			$this->eotThirdLevelArchitectForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->eotThirdLevelContractorForm->validate($inputs);
		}

		$this->eotThirdLevelMessageRepo->add($user, $eot, $inputs);

		Flash::success('Successfully replied Step Three Message!');

		return Redirect::route('eot.show', array( $eot->project_id, $eot->id ));
	}

}