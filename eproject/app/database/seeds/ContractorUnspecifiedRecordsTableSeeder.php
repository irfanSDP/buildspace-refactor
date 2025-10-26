<?php

class ContractorUnspecifiedRecordsTableSeeder extends Seeder {

	public function run()
	{
        $timestamp = Carbon\Carbon::now();

        $records = array();
        $records[] = array(
            'name' => 'Unspecified',
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        );
        \PCK\WorkCategories\WorkCategory::insert($records);

        \PCK\WorkCategories\WorkSubcategory::insert($records);

        \PCK\Contractors\ContractorDetails\RegistrationStatus::insert($records);

        $records = array();
        $records[] = array(
            'grade' => 'Unspecified',
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        );
        \PCK\CPEGrades\CurrentCPEGrade::insert($records);

        \PCK\CPEGrades\PreviousCPEGrade::insert($records);
	}
}