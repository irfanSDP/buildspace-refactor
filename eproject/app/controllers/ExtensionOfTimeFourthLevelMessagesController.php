<?php

use PCK\ContractGroups\Types\Role;
use PCK\Forms\EOTMessageFourthLevelArchitectForm;
use PCK\Forms\EOTMessageFourthLevelContractorForm;
use PCK\ExtensionOfTimes\ExtensionOfTimeRepository;
use PCK\ExtensionOfTimeFourthLevelMessages\ExtensionOfTimeFourthLevelMessageRepository;

class ExtensionOfTimeFourthLevelMessagesController extends \BaseController {

	private $eotRepo;

	private $eotFourthLevelMessageRepo;

	private $eotMessageFourthLevelArchitectForm;

	private $eotMessageFourthLevelContractorForm;

	public function __construct(
		ExtensionOfTimeRepository $eotRepo,
		ExtensionOfTimeFourthLevelMessageRepository $eotFourthLevelMessageRepo,
		EOTMessageFourthLevelArchitectForm $eotMessageFourthLevelArchitectForm,
		EOTMessageFourthLevelContractorForm $eotMessageFourthLevelContractorForm
	)
	{
		$this->eotRepo                             = $eotRepo;
		$this->eotFourthLevelMessageRepo           = $eotFourthLevelMessageRepo;
		$this->eotMessageFourthLevelArchitectForm  = $eotMessageFourthLevelArchitectForm;
		$this->eotMessageFourthLevelContractorForm = $eotMessageFourthLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Extension Of Time Fourth Level Message.
	 *
	 * @param $project
	 * @param $eotId
	 * @return Response
	 */
	public function create($project, $eotId)
	{
		$user           = Confide::user();
		$eot            = $this->eotRepo->find($project, $eotId);
		$eotLastMessage = $this->eotFourthLevelMessageRepo->checkLatestMessagePosterRole($eot->id);
		$uploadedFiles  = $this->getAttachmentDetails();

		return View::make('extension_of_time_fourth_level_messages.create', compact('user', 'eot', 'eotLastMessage', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Extension Of Time Fourth Level Message in storage.
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

		if ( $user->hasCompanyProjectRole($project, Role::INSTRUCTION_ISSUER) )
		{
			$this->eotMessageFourthLevelArchitectForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->eotMessageFourthLevelContractorForm->validate($inputs);
		}

		$this->eotFourthLevelMessageRepo->add($user, $eot, $inputs);

		Flash::success('Successfully replied Step Four Message!');

		return Redirect::route('eot.show', array( $eot->project_id, $eot->id ));
	}

}