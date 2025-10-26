<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

class ContractGroupsTableSeeder_ContractGroupCategoriesTableSeeder_addConsultants3And4And5 extends Seeder {

    public function run()
    {
        $this->addConsultant3();
        $this->addConsultant4();
        $this->addConsultant5();

        ProjectRolesTableSeeder::initialiseAllProjects(array( Role::CONSULTANT_3, Role::CONSULTANT_4, Role::CONSULTANT_5 ));
    }

    private function addConsultant3()
    {
        if( ! $contractGroup = ContractGroup::where('group', '=', Role::CONSULTANT_3)->first() )
        {
            $contractGroup        = new ContractGroup;
            $contractGroup->group = Role::CONSULTANT_3;
            $contractGroup->name  = ContractGroup::getSystemDefaultGroupName(Role::CONSULTANT_3);
            $contractGroup->save();
        }

        return $contractGroup;
    }

    private function addConsultant4()
    {
        if( ! $contractGroup = ContractGroup::where('group', '=', Role::CONSULTANT_4)->first() )
        {
            $contractGroup        = new ContractGroup;
            $contractGroup->group = Role::CONSULTANT_4;
            $contractGroup->name  = ContractGroup::getSystemDefaultGroupName(Role::CONSULTANT_4);
            $contractGroup->save();
        }

        return $contractGroup;
    }

    private function addConsultant5()
    {
        if( ! $contractGroup = ContractGroup::where('group', '=', Role::CONSULTANT_5)->first() )
        {
            $contractGroup        = new ContractGroup;
            $contractGroup->group = Role::CONSULTANT_5;
            $contractGroup->name  = ContractGroup::getSystemDefaultGroupName(Role::CONSULTANT_5);
            $contractGroup->save();
        }

        return $contractGroup;
    }

}