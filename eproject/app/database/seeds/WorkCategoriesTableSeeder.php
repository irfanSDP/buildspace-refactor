<?php

use PCK\WorkCategories\WorkCategory;

class WorkCategoriesTableSeeder extends Seeder {

    public function run()
    {
        $categoryNames = [
            'Building',
            'Civil',
            'Landscape',
            'MEP',
            'Unspecified',
        ];

        $workCategories = array();
        $timestamp = Carbon\Carbon::now();
        foreach($categoryNames as $categoryName)
        {
            $workCategories[] = array(
                'name' => $categoryName,
                'identifier' => WorkCategory::generateIdentifier($categoryName),
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            );
        }
        WorkCategory::insert($workCategories);
    }
}