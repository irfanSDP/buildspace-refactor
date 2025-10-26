<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

class ContractGroupCategoriesTableSeeder_addContractor extends Seeder {

    public function run()
    {
        if( ! ( $projectManagerCategory = ContractGroupCategory::where('name', '=', ContractGroupCategory::CONTRACTOR_NAME)->first() ) )
        {
            $projectManagerCategory       = new ContractGroupCategory;
            $projectManagerCategory->name = ContractGroupCategory::CONTRACTOR_NAME;
        }

        $projectManagerCategory->editable = false;
        $projectManagerCategory->save();

        // Default Relation.
        $projectManagerCategory->contractGroups()->sync(array( ContractGroup::getIdByGroup(Role::CONTRACTOR) ));
    }

}