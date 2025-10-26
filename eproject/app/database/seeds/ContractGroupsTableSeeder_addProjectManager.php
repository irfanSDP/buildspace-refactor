<?php

use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\Contracts\Contract;

class ContractGroupsTableSeeder_addProjectManager extends Seeder {

    public function run()
    {
        if( $this->dataExists() ) return;

        $projectManagerContractGroup        = new ContractGroup;
        $projectManagerContractGroup->group = Role::PROJECT_MANAGER;
        $projectManagerContractGroup->name  = ContractGroup::getSystemDefaultGroupName(Role::PROJECT_MANAGER);
        $projectManagerContractGroup->save();
    }

    private function dataExists()
    {
        return ( ContractGroup::where('group', '=', Role::PROJECT_MANAGER)->count() > 0 );
    }
}