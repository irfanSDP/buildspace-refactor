<?php

use PCK\Contractors\ContractorDetails\RegistrationStatus;

class ContractorRegistrationStatusesTableSeeder extends Seeder {

    public function run()
    {
        $categoryNames = [
            'New Register',
            'Approved',
            'Non-Register',
            'Unspecified',
        ];

        $registrationStatuses = array();
        $timestamp = Carbon\Carbon::now();
        foreach($categoryNames as $categoryName)
        {
            $registrationStatuses[] = array(
                'name' => $categoryName,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            );
        }
        RegistrationStatus::insert($registrationStatuses);
    }
}