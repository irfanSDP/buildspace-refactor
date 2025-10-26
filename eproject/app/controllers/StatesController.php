<?php

use PCK\States\StateRepository;
use PCK\Countries\CountryRepository;

class StatesController extends BaseController {

	private $countryRepo;

	private $stateRepo;

	public function __construct(CountryRepository $countryRepo, StateRepository $stateRepo)
	{
		$this->countryRepo = $countryRepo;
		$this->stateRepo   = $stateRepo;
	}

	/**
	 * Display the view of all current user listing
	 *
	 * @param $countryId
	 * @return string
	 */
	public function index($countryId)
	{
		$country = $this->countryRepo->findStatesWithCountryById($countryId);

		return View::make('states.index', compact('country'));
	}

	/**
	 * Displays the form for account creation
	 *
	 * @param $countryId
	 * @return  Illuminate\Http\Response
	 */
	public function create($countryId)
	{
		$currentUser = \Confide::user();
		$country     = $this->countryRepo->find($countryId);

		return View::make('states.create', array(
			'pageTitle'   => 'Create New Country',
			'currentUser' => $currentUser,
			'country'     => $country,
		));
	}

	/**
	 * Stores new account
	 *
	 * @param $countryId
	 * @return  Illuminate\Http\Response
	 */
	public function store($countryId)
	{
		$country             = $this->countryRepo->find($countryId);
		$input               = Input::all();
		$input['country_id'] = $country->id;

		$this->stateRepo->add($input);

		Flash::success("State {$input['name']} successfully added!");

		return Redirect::route('states', array( $country->id ));
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /countries/{id}/edit
	 *
	 * @param  int $countryId
	 * @param  int $statedId
	 * @return Response
	 */
	public function edit($countryId, $statedId)
	{
		$user    = \Confide::user();
		$country = $this->countryRepo->find($countryId);
		$state   = $this->stateRepo->find($statedId);

		return View::make('states.edit', compact('user', 'country', 'state'));
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /countries/{id}
	 *
	 * @param  int $countryId
	 * @param  int $statedId
	 * @return Response
	 */
	public function update($countryId, $statedId)
	{
		$country = $this->countryRepo->find($countryId);
		$state   = $this->stateRepo->find($statedId);

		$input               = Input::all();
		$input['country_id'] = $country->id;

		$state = $this->stateRepo->update($state, $input);

		Flash::success("State {$state->name} successfully updated!");

		return Redirect::route('states', array( $country->id ));
	}

}