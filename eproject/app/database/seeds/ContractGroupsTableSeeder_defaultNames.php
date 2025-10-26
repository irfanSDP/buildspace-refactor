<?php

use PCK\ContractGroups\ContractGroup;

class ContractGroupsTableSeeder_defaultNames extends Seeder {

    public function run()
    {
        foreach(ContractGroup::all() as $contractGroup)
        {
            $contractGroup->name = ContractGroup::getSystemDefaultGroupName($contractGroup->group);
            $contractGroup->save();
        }
    }

}