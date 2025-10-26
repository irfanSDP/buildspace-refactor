<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class AddNewCountryForm extends FormValidator {

	/**
	 * Validation rules for creating Company
	 *
	 * @var array
	 */
	protected $rules = [
		'country'       => 'required',
		'iso'           => 'required',
		'iso3'          => 'required',
		'fips'          => 'required',
		'continent'     => 'required',
		'currency_code' => 'required',
		'currency_name' => 'required',
		'phone_prefix'  => 'required',
		'postal_code'   => 'required',
		'languages'     => 'required',
		'geonameid'     => 'required'
	];

}