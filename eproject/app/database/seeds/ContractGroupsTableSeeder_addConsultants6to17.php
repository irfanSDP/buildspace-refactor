<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

class ContractGroupsTableSeeder_addConsultants6to17 extends Seeder {

    public function run()
    {
        $roles = [
            Role::CONSULTANT_6,
            Role::CONSULTANT_7,
            Role::CONSULTANT_8,
            Role::CONSULTANT_9,
            Role::CONSULTANT_10,
            Role::CONSULTANT_11,
            Role::CONSULTANT_12,
            Role::CONSULTANT_13,
            Role::CONSULTANT_14,
            Role::CONSULTANT_15,
            Role::CONSULTANT_16,
            Role::CONSULTANT_17,
        ];

        foreach($roles as $role)
        {
            $this->addRole($role);
        }

        ProjectRolesTableSeeder::initialiseAllProjects($roles);
    }

    protected function addRole($role)
    {
        if( ! $contractGroup = ContractGroup::where('group', '=', $role)->first() )
        {
            $contractGroup        = new ContractGroup;
            $contractGroup->group = $role;
            $contractGroup->name  = ContractGroup::getSystemDefaultGroupName($role);
            $contractGroup->save();
        }
    }
}