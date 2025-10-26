<?php

use PCK\MyCompanyProfiles\MyCompanyProfile;

class MyCompanyProfileTableSeeder extends Seeder {

	public function run()
	{
		MyCompanyProfile::create([
			'name' => 'Web Claim'
		]);
	}

}