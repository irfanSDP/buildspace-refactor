<?php

use PCK\Forms\EOTClaimForm;
use PCK\ExtensionOfTimes\ExtensionOfTimeRepository;
use PCK\ExtensionOfTimeClaims\ExtensionOfTimeClaimRepository;

class ExtensionOfTimeClaimsController extends \BaseController {

	private $eotRepo;

	private $eotClaimRepo;

	private $eotClaimForm;

	public function __construct(
		ExtensionOfTimeRepository $eotRepo,
		ExtensionOfTimeClaimRepository $eotClaimRepo,
		EOTClaimForm $eotClaimForm
	)
	{
		$this->eotRepo      = $eotRepo;
		$this->eotClaimRepo = $eotClaimRepo;
		$this->eotClaimForm = $eotClaimForm;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param $project
	 * @param $eotId
	 * @return Response
	 */
	public function create($project, $eotId)
	{
		$user          = Confide::user();
		$eot           = $this->eotRepo->find($project, $eotId);
		$uploadedFiles = $this->getAttachmentDetails();

		return View::make('extension_of_time_claims.create', compact('user', 'eot', 'uploadedFiles'));
	}

	/**
	 * Store a newly created resource in storage.
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

		$this->eotClaimForm->validate($inputs);

		$this->eotClaimRepo->add($user, $eot, $inputs);

		Flash::success('Successfully added Final Claim!');

		return Redirect::route('eot.show', array( $eot->project_id, $eot->id ));
	}

}