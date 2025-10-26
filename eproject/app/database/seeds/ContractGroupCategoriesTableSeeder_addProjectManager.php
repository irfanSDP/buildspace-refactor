<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

class ContractGroupCategoriesTableSeeder_addProjectManager extends Seeder {

    public function run()
    {
        if( ! ( $projectManagerCategory = ContractGroupCategory::where('name', '=', ContractGroupCategory::PROJECT_MANAGER_NAME)->first() ) )
        {
            $projectManagerCategory       = new ContractGroupCategory;
            $projectManagerCategory->name = ContractGroupCategory::PROJECT_MANAGER_NAME;
        }

        $projectManagerCategory->editable = false;
        $projectManagerCategory->save();

        // Default Relation.
        $projectManagerCategory->contractGroups()->sync(array( ContractGroup::getIdByGroup(Role::PROJECT_MANAGER) ));
    }

}