<?php

use PCK\ContractGroupCategory\ContractGroupCategory;

class ContractGroupCategoryIdOnCompaniesTableSeeder extends Seeder {

    /**
     * Assigns all unassigned companies to the 'Contractor' Contract Group Category.
     */
    public function run()
    {
        // Creates a ContractGroupCategory named Contractor.
        if( is_null(ContractGroupCategory::where('name', '=', ContractGroupCategory::CONTRACTOR_NAME)->first()) )
        {
            \DB::table('contract_group_categories')->insert(array(
                0 =>
                    array(
                        'name'       => ContractGroupCategory::CONTRACTOR_NAME,
                        'editable'   => true,
                        'created_at' => 'now()',
                        'updated_at' => 'now()',
                    )
            ));
        }

        $contractorContractGroupCategory = ContractGroupCategory::where('name', '=', ContractGroupCategory::CONTRACTOR_NAME)->first();

        foreach(\PCK\Companies\Company::all() as $company)
        {
            if( ! is_null($company->contract_group_category_id) ) continue;

            $company->contract_group_category_id = $contractorContractGroupCategory->id;
            $company->save();
        }
    }
}