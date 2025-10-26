<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

class ContractGroupCategoriesTableSeeder extends Seeder {

    public function run()
    {
        if( $this->dataExists() )
        {
            return;
        }

        \DB::table('contract_group_categories')->insert(array(
            0 =>
                array(
                    'name'       => ContractGroupCategory::BUSINESS_UNIT_NAME,
                    'editable'   => false,
                    'created_at' => 'now()',
                    'updated_at' => 'now()',
                ),
            1 =>
                array(
                    'name'       => ContractGroupCategory::GROUP_CONTRACT_DIVISION_NAME,
                    'editable'   => false,
                    'created_at' => 'now()',
                    'updated_at' => 'now()',
                ),
        ));

        // Default relations
        $businessUnitCategory = ContractGroupCategory::where('name', '=', ContractGroupCategory::BUSINESS_UNIT_NAME)->first();
        $businessUnitCategory->contractGroups()->sync(array( ContractGroup::getIdByGroup(Role::PROJECT_OWNER) ));

        $groupContractDivisionCategory = ContractGroupCategory::where('name', '=', ContractGroupCategory::GROUP_CONTRACT_DIVISION_NAME)->first();
        $groupContractDivisionCategory->contractGroups()->sync(array( ContractGroup::getIdByGroup(Role::GROUP_CONTRACT) ));
    }

    private function dataExists()
    {
        $businessUnitCategory = ContractGroupCategory::where('name', '=', ContractGroupCategory::BUSINESS_UNIT_NAME)
            ->first();

        $groupContractDivisionCategory = ContractGroupCategory::where('name', '=', ContractGroupCategory::GROUP_CONTRACT_DIVISION_NAME)
            ->first();

        if( $businessUnitCategory && $groupContractDivisionCategory )
        {
            return true;
        }

        if( empty( $businessUnitCategory ) || empty( $groupContractDivisionCategory ) )
        {
            // We delete those that exist and start over.
            if($businessUnitCategory)
            {
                $businessUnitCategory->contractGroups()->sync(array());
                $businessUnitCategory->delete();
            }

            if($groupContractDivisionCategory)
            {
                $groupContractDivisionCategory->contractGroups()->sync(array());
                $groupContractDivisionCategory->delete();
            }
        }


        return false;
    }
}