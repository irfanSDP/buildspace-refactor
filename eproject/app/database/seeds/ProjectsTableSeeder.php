<?php

use Faker\Factory as Faker;
use PCK\Contracts\Contract;
use PCK\Projects\Project;

class ProjectsTableSeeder extends Seeder {

	public function run()
	{
		$faker     = Faker::create();
		$timestamp = Carbon\Carbon::now();
		$projects  = array();

		foreach ( range(1, 10) as $index )
		{
			$projects[] = array(
				'contract_id'      => Contract::findByType(Contract::TYPE_PAM2006)->id,
				'title'            => $faker->sentence(),
				'reference'        => $faker->word,
				'address'          => $faker->address,
				'description'      => $faker->text(),
				'employer_name'    => $faker->sentence(),
				'employer_address' => $faker->address,
				'created_at'       => $timestamp,
				'updated_at'       => $timestamp,
				'created_by'       => 1,
				'updated_by'       => 1,
			);
		}

		Project::insert($projects);
	}

}