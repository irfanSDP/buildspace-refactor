<?php namespace PCK\Countries;

class CountryRepository {

	/**
	 * @var Country
	 */
	private $country;

	public function __construct(Country $country)
	{
		$this->country = $country;
	}

	/**
	 * Get available Countries listing
	 *
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function all()
	{
		return $this->country->orderBy('id', 'desc')->get();
	}

	/**
	 * Find country's related information by ID
	 *
	 * @param $id
	 * @return \Illuminate\Support\Collection|static
	 */
	public function find($id)
	{
		return $this->country->findOrFail($id);
	}

	/**
	 * Find user(s) related with country by ID
	 *
	 * @param $countryId
	 * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|static
	 */
	public function findStatesWithCountryById($countryId)
	{
		return $this->country->with('states')->findOrFail($countryId);
	}

	/**
	 * Create new record of country
	 *
	 * @param $inputs
	 * @return bool
	 */
	public function add(array $inputs)
	{
		$this->country->country       = $inputs['country'];
		$this->country->iso           = $inputs['iso'];
		$this->country->iso3          = $inputs['iso3'];
		$this->country->fips          = $inputs['fips'];
		$this->country->continent     = $inputs['continent'];
		$this->country->currency_code = $inputs['currency_code'];
		$this->country->currency_name = $inputs['currency_name'];
		$this->country->phone_prefix  = $inputs['phone_prefix'];
		$this->country->postal_code   = $inputs['postal_code'];
		$this->country->languages     = $inputs['languages'];
		$this->country->geonameid     = $inputs['geonameid'];
		$this->country->save();

		$currencySetting = new CurrencySetting();
		$currencySetting->country_id = $this->country->id;
		$currencySetting->rounding_type = $inputs['rounding_type'];
		$currencySetting->save();
	}

	/**
	 * Update existing record of selected country
	 *
	 * @param Country $country
	 * @param         $input
	 * @return Country
	 */
	public function update(Country $country, $input)
	{
		$country->country       = $input['country'];
		$country->iso           = $input['iso'];
		$country->iso3          = $input['iso3'];
		$country->fips          = $input['fips'];
		$country->continent     = $input['continent'];
		$country->currency_code = $input['currency_code'];
		$country->currency_name = $input['currency_name'];
		$country->phone_prefix  = $input['phone_prefix'];
		$country->postal_code   = $input['postal_code'];
		$country->languages     = $input['languages'];
		$country->geonameid     = $input['geonameid'];
		$country->save();

		$currencySetting = $country->currencySetting;
		$currencySetting->rounding_type = $input['rounding_type'];
		$currencySetting->save();

		return $country;
	}

	public function getDefaultCountry()
	{
		$defaultCountry = Country::where('country', '=', getenv('DEFAULT_COUNTRY'))->first();

        return ( $defaultCountry ) ? $defaultCountry : Country::select('id')->first();
	}

	public function getCountryCurrencies()
	{
	    $records = [];

	    foreach(Country::orderBy('country')->get() as $country)
	    {
	        $records[$country->id] = trim($country->currency_code) . " (" . trim($country->country) . ")";
	    }

	    return $records;
	}
}