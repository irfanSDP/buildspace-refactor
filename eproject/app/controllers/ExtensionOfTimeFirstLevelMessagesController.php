<?php

use PCK\ContractGroups\Types\Role;
use PCK\Forms\EOTMessageFirstLevelArchitectForm;
use PCK\Forms\EOTMessageFirstLevelContractorForm;
use PCK\ExtensionOfTimes\ExtensionOfTimeRepository;
use PCK\ExtensionOfTimeFirstLevelMessages\ExtensionOfTimeFirstLevelMessageRepository;

class ExtensionOfTimeFirstLevelMessagesController extends \BaseController {

	private $eotRepo;

	private $eotFirstLevelMessageRepo;

	private $eotFirstLevelArchitectForm;

	private $eotFirstLevelContractorForm;

	public function __construct(
		ExtensionOfTimeRepository $eotRepo,
		ExtensionOfTimeFirstLevelMessageRepository $eotFirstLevelMessageRepo,
		EOTMessageFirstLevelArchitectForm $eotFirstLevelArchitectForm,
		EOTMessageFirstLevelContractorForm $eotFirstLevelContractorForm
	)
	{
		$this->eotRepo                     = $eotRepo;
		$this->eotFirstLevelMessageRepo    = $eotFirstLevelMessageRepo;
		$this->eotFirstLevelArchitectForm  = $eotFirstLevelArchitectForm;
		$this->eotFirstLevelContractorForm = $eotFirstLevelContractorForm;
	}

	/**
	 * Show the form for creating a new Extension Of Time First Level Message.
	 *
	 * @param $project
	 * @param $eotId
	 * @return Response
	 */
	public function create($project, $eotId)
	{
		$user          = \Confide::user();
		$eot           = $this->eotRepo->find($project, $eotId);
		$uploadedFiles = $this->getAttachmentDetails();

		return View::make('extension_of_time_first_level_messages.create', compact('user', 'eot', 'uploadedFiles'));
	}

	/**
	 * Store a newly created Extension Of Time First Level Message in storage.
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
			$this->eotFirstLevelArchitectForm->validate($inputs);
		}

		if ( $user->hasCompanyProjectRole($project, Role::CONTRACTOR) )
		{
			$this->eotFirstLevelContractorForm->validate($inputs);
		}

		$this->eotFirstLevelMessageRepo->add($user, $eot, $inputs);

		Flash::success('Successfully replied Step One Message!');

		return Redirect::route('eot.show', array( $eot->project_id, $eot->id ));
	}

}