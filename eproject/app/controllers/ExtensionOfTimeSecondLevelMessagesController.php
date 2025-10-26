<?php

use PCK\Calendars\CalendarRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Forms\EOTMessageSecondLevelArchitectForm;
use PCK\Forms\EOTMessageSecondLevelContractorForm;
use PCK\ExtensionOfTimes\ExtensionOfTimeRepository;
use PCK\ExtensionOfTimeSecondLevelMessages\ExtensionOfTimeSecondLevelMessageRepository;

class ExtensionOfTimeSecondLevelMessagesController extends \BaseController {

	private $eotRepo;

	private $eotSecondLevelMessageRepo;

	private $calendarRepository;

	private $eotSecondLevelArchitectForm;

	private $eotSecondLevelContractorForm;

	public function __construct(
		ExtensionOfTimeRepository $eotRepo,
		ExtensionOfTimeSecondLevelMessageRepository $eotSecondLevelMessageRepo,
		CalendarRepository $calendarRepository,
		EOTMessageSecondLevelArchitectForm $eotSecondLevelArchitectForm,
		EOTMessageSecondLevelContractorForm $eotSecondLevelContractorForm
	)
	{
		$this->eotRepo                      = $eotRepo;
		$this->eotSecondLevelMessageRepo    = $eotSecondLevelMessageRepo;
		$this->calendarRepository           = $calendarRepository;
		$this->eotSecondLevelArchitectForm  = $eotSecondLevelArchitectForm;
		$this->eotSecondLevelContractorForm = $eotSecondLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Extension Of Time Second Level Message.
	 *
	 * @param $project
	 * @param $eotId
	 * @return Response
	 */
	public function create($project, $eotId)
	{
		$user           = Confide::user();
		$eot            = $this->eotRepo->find($project, $eotId);
		$eotLastMessage = $this->eotSecondLevelMessageRepo->checkLatestMessagePosterRole($eot->id);
		$events         = $this->calendarRepository->getEventsListing($eot->project);
		$uploadedFiles  = $this->getAttachmentDetails();

		JavaScript::put(compact('events'));

		return View::make('extension_of_time_second_level_messages.create', compact('user', 'eot', 'eotLastMessage', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Extension Of Time Second Level Message in storage.
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

        $inputs['requested_new_deadline']   = $project->getAppTimeZoneTime($inputs['requested_new_deadline'] ?? null);
        $inputs['grant_different_deadline'] = $project->getAppTimeZoneTime($inputs['grant_different_deadline'] ?? null);

		if ( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
		{
			$this->eotSecondLevelArchitectForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->eotSecondLevelContractorForm->validate($inputs);
		}

		$this->eotSecondLevelMessageRepo->add($user, $eot, $inputs);

		Flash::success('Successfully replied Step Two Message!');

		return Redirect::route('eot.show', array( $eot->project_id, $eot->id ));
	}

}