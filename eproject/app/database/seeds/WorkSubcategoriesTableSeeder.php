<?php

use PCK\WorkCategories\WorkSubcategory;

class WorkSubcategoriesTableSeeder extends Seeder {

    public function run()
    {
        $categoryNames = [
            'Terrace',
            'Commercial',
            'Factory',
            'Highrise',
            'Railway',
            'Unspecified',
        ];

        $workSubcategories = array();
        $timestamp = Carbon\Carbon::now();
        foreach($categoryNames as $categoryName)
        {
            $workSubcategories[] = array(
                'name' => $categoryName,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            );
        }
        WorkSubcategory::insert($workSubcategories);
    }
}