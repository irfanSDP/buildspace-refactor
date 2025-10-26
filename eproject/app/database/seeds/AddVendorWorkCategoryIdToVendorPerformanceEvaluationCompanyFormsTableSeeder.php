<?php

use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;
use PCK\Vendor\Vendor;
use PCK\Helpers\DBTransaction;

class AddVendorWorkCategoryIdToVendorPerformanceEvaluationCompanyFormsTableSeeder extends Seeder {

    protected $companyVendorWorkCategories = [];

    public function run()
    {
        ini_set('memory_limit', '2048M');

        // vendor_work_category_id should be nullable for now.

        // Assign company_form vendor_work_category.
        // The original form will be set to have the same vendor_work_category as the setup with a form template.
        // No new vendor_performance_evaluation_company_forms will be created for the other vendor_work_categories.

        // VPE vendors without vendor_work_categories.
        // We should first check if there are any VPE vendors without vendor_work_categories.
        // This should only be run if we're okay with removing (hard delete) the forms for these vendors.

        $this->loadCompanyVendorWorkCategories();

        // Update deleted forms
        // $forms = VendorPerformanceEvaluationCompanyForm::withTrashed()->whereNotNull('deleted_at')->forceDelete();

        $forms = VendorPerformanceEvaluationCompanyForm::withTrashed()->get();

        $totalForms = $forms->count();

        $transaction = new DBTransaction();

        $transaction->begin();

        try
        {
            foreach($forms as $form)
            {
                $companyVendorWorkCategories = $this->companyVendorWorkCategories[$form->company_id] ?? [];

                if(empty($companyVendorWorkCategories))
                {
                    $form->forceDelete();

                    continue;
                }

                $setup = VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $form->vendor_performance_evaluation_id)
                    ->where('company_id', '=', $form->company_id)
                    ->first();

                if(!$setup)
                {
                    $form->forceDelete();
                    continue;
                }

                \DB::statement("UPDATE vendor_performance_evaluation_company_forms SET vendor_work_category_id = {$setup->vendor_work_category_id} WHERE id = {$form->id};");
            }

            $transaction->commit();
        }
        catch(\Exception $e)
        {
            $transaction->rollback();
            print_r($e->getMessage());
            print_r(PHP_EOL);
            print_r($e->getTraceAsString());
            print_r(PHP_EOL);
        }
    }

    protected function loadCompanyVendorWorkCategories()
    {
        $companyIds = VendorPerformanceEvaluationCompanyForm::lists('company_id');

        $vendors = Vendor::whereIn('company_id', $companyIds)->get();

        foreach($vendors as $vendor)
        {
            if(!array_key_exists($vendor->company_id, $this->companyVendorWorkCategories)) $this->companyVendorWorkCategories[$vendor->company_id] = [];

            $this->companyVendorWorkCategories[$vendor->company_id][] = $vendor->vendor_work_category_id;
        }
    }
}