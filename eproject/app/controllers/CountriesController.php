<?php

use PCK\States\State;
use PCK\Countries\Country;
use PCK\Forms\AddNewCountryForm;
use PCK\Countries\CountryRepository;

class CountriesController extends BaseController {

	private $countryRepo;

	private $addNewCountryForm;

	public function __construct(CountryRepository $countryRepo, AddNewCountryForm $addNewCountryForm)
	{
		$this->countryRepo       = $countryRepo;
		$this->addNewCountryForm = $addNewCountryForm;
	}

	/**
	 * Display a listing of the resource.
	 * GET /countries
	 *
	 * @return Response
	 */
	public function index()
	{
		$countries = $this->countryRepo->all();
		$user      = \Confide::user();

		return View::make('countries.index', compact('countries', 'user'));
	}


	/**
	 * Show the form for creating a new resource.
	 * GET /countries/create
	 *
	 * @return Response
	 */
	public function create()
	{
		return View::make('countries.create');
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /countries
	 *
	 * @return Response
	 */
	public function store()
	{
		$input = Input::all();

		$this->addNewCountryForm->validate($input);

		$this->countryRepo->add($input);

		Flash::success("Country {$input['country']} successfully added!");

		return Redirect::route('countries');
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /countries/{id}/edit
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function edit($id)
	{
		$user    = \Confide::user();
		$country = $this->countryRepo->find($id);

		return View::make('countries.edit', compact('user', 'country'));
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /countries/{id}
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function update($id)
	{
		$country = $this->countryRepo->find($id);
		$input   = Input::all();

		$this->addNewCountryForm->validate($input);

		$country = $this->countryRepo->update($country, $input);

		Flash::success("Country {$country->country} successfully updated!");

		return Redirect::route('countries');
	}

	public function getAllCountries($input = null)
	{
		$term = $input ? : Input::get('q');

		$defaultCountry = $this->countryRepo->getDefaultCountry()->toArray();

		$countries = Country::selectRaw('id, TRIM(country) AS text')
			->whereRaw('LOWER(country) ILIKE ?', array( '%' . strtolower($term) . '%' ))
			->get()
			->toArray();

		return Response::json(array(
			'success' => true,
			'default' => $defaultCountry['id'],
			'data'    => $countries
		));
	}

	public function getStateByCountryId($id = null)
	{
		$countryId = $id ? : Input::get('countryId');

		$data = Country::find($countryId)->states()
			->selectRaw('id, TRIM(name) AS text')
			->get()
			->toArray();

		$success = true;

		return Response::json(compact('success', 'data'));
	}

	public function getEventByCountryId($id)
	{
		return State::find($id)->events()->get()->toArray();
	}

	public function getStatesByCountry()
	{
		$countryId = Input::get('countryId');
		$states = [];

		foreach(Country::find($countryId)->states()->orderBy('id', 'ASC')->get() as $state)
		{
			array_push($states, [
				'id'   => $state->id,
				'name' => trim($state->name),
			]);
		}

		return Response::json($states);
	}
}