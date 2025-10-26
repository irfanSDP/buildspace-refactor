<?php

use PCK\RequestForVariation\RequestForVariationCategory;

class AddRequestForVariationCategoriesTableSeeder extends Seeder {

    public function run()
    {
        $categories = [
            'Upgrading / Design Enhancement',
            'Due to Site Condition',
            'Adjustment to Contract',
            'Overlook',
            'Required by Authority'
        ];

        foreach($categories as $name)
        {
            RequestForVariationCategory::create(array('name' => $name));
        }
    }
}
