<?php

use PCK\Countries\Country;
use PCK\Countries\CurrencySetting;

class CurrencySettingsTableSeeder extends Seeder {

	public function run()
	{
		foreach(Country::all() as $country)
		{
			if($country->currencySetting) continue;

			$currencysetting = new CurrencySetting();
			$currencysetting->country_id = $country->id;
			$currencysetting->rounding_type = CurrencySetting::ROUNDING_TYPE_DISABLED;
			$currencysetting->save();
		}
	}
}