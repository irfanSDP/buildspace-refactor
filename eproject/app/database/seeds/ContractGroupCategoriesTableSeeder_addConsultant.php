<?php

use PCK\ContractGroupCategory\ContractGroupCategory;

class ContractGroupCategoriesTableSeeder_addConsultant extends Seeder {

    public function run()
    {
        if( ! ( $projectManagerCategory = ContractGroupCategory::where('name', '=', ContractGroupCategory::CONSULTANT_NAME)->first() ) )
        {
            $projectManagerCategory       = new ContractGroupCategory;
            $projectManagerCategory->name = ContractGroupCategory::CONSULTANT_NAME;
        }

        $projectManagerCategory->editable = false;
        $projectManagerCategory->save();
    }

}