<?php

use Faker\Factory as Faker;
use PCK\Companies\Company;

class CompaniesTableSeeder extends Seeder {

    public function run()
    {
        $faker = Faker::create();
        $timestamp = Carbon\Carbon::now();
        $companies = array();

        $companies[] = array(
            'name'                       => $faker->company,
            'address'                    => $faker->address,
            'main_contact'               => $faker->name,
            'email'                      => $faker->companyEmail,
            'telephone_number'           => $faker->phoneNumber,
            'fax_number'                 => $faker->phoneNumber,
            'pam_2006_contract_group_id' => 1,
            'created_at'                 => $timestamp,
            'updated_at'                 => $timestamp,
        );

        Company::insert($companies);
    }

}