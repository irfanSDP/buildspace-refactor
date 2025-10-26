<?php

use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;

class ContractGroupsTableSeeder extends Seeder {

    public function run()
    {
        ContractGroup::firstOrCreate(array(
            'group' => Role::INSTRUCTION_ISSUER
        ));

        ContractGroup::firstOrCreate(array(
            'group' => Role::CONTRACTOR
        ));

        ContractGroup::firstOrCreate(array(
            'group' => Role::CLAIM_VERIFIER
        ));

        ContractGroup::firstOrCreate(array(
            'group' => Role::CONSULTANT_1
        ));

        ContractGroup::firstOrCreate(array(
            'group' => Role::CONSULTANT_2
        ));

        ContractGroup::firstOrCreate(array(
            'group' => Role::PROJECT_OWNER
        ));

        ContractGroup::firstOrCreate(array(
            'group' => Role::GROUP_CONTRACT
        ));
    }

}