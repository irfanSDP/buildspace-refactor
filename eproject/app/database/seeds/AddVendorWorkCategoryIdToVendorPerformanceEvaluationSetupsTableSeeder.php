<?php

use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;
use PCK\Vendor\Vendor;
use PCK\Helpers\DBTransaction;

class AddVendorWorkCategoryIdToVendorPerformanceEvaluationSetupsTableSeeder extends Seeder {

    protected $companyVendorWorkCategories = [];

    public function run()
    {
        ini_set('memory_limit', '2048M');
        // vendor_work_category_id should be nullable for now.

        // Duplicate setups.
        // The original setup will be assigned one vendor work category from the vendor.
        // New setups will be created, one for each of the remaining vendor_work_categories.

        // VPE vendors without vendor_work_categories.
        // We should first check if there are any VPE vendors without vendor_work_categories.
        // This should only be run if we're okay with removing the setups for these vendors.

        $this->loadCompanyVendorWorkCategories();

        $setups = VendorPerformanceEvaluationSetup::all();

        $transaction = new DBTransaction();

        $transaction->begin();

        try
        {
            foreach($setups as $setup)
            {
                $companyVendorWorkCategories = $this->companyVendorWorkCategories[$setup->company_id] ?? [];

                if(empty($companyVendorWorkCategories))
                {
                    print_r("Removing setup for Company {$setup->company_id}");
                    print_r(PHP_EOL);

                    $setup->delete();
                    continue;
                }

                // Add a random (the first) vendor work category to the original setup.
                $firstVendorWorkCategoryId = array_shift($companyVendorWorkCategories);

                \DB::statement("UPDATE vendor_performance_evaluation_setups SET vendor_work_category_id = {$firstVendorWorkCategoryId} WHERE id = {$setup->id};");

                // Duplicate setups. Create one for each of the other vendor_work_categories.
                foreach($companyVendorWorkCategories as $vendorWorkCategoryId)
                {
                    $newSetup = new VendorPerformanceEvaluationSetup;

                    $newSetup->vendor_performance_evaluation_id = $setup->vendor_performance_evaluation_id;
                    $newSetup->company_id = $setup->company_id;
                    $newSetup->created_at = $setup->created_at;
                    $newSetup->updated_at = $setup->updated_at;
                    $newSetup->vendor_work_category_id = $vendorWorkCategoryId;
                    $newSetup->save();
                }
            }

            $this->updateEvaluationSetups();

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
        $companyIds = VendorPerformanceEvaluationSetup::lists('company_id');

        $vendors = Vendor::whereIn('company_id', $companyIds)->get();

        foreach($vendors as $vendor)
        {
            if(!array_key_exists($vendor->company_id, $this->companyVendorWorkCategories)) $this->companyVendorWorkCategories[$vendor->company_id] = [];

            $this->companyVendorWorkCategories[$vendor->company_id][] = $vendor->vendor_work_category_id;
        }
    }

    protected function updateEvaluationSetups()
    {
        $evaluations = VendorPerformanceEvaluation::has('project')->withTrashed()->get();

        foreach($evaluations as $evaluation)
        {
            $this->syncVendorWorkCategorySetups($evaluation);
        }
    }

    /**
     * Initialise all other setups.
     * Create new setups for previously non-existing setup.
    */
    protected function syncVendorWorkCategorySetups($evaluation)
    {
        $assignedCompanies = $evaluation->project->selectedCompanies;

        // check if any. Hopefully none.
        $count = VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $evaluation->id)
            ->whereNotIn('company_id', $assignedCompanies->lists('id'))
            ->count();

        if($count > 0)
        {
            print_r("No longer assigned. Removing {$count} setups from evaluation {$evaluation->id}");
            print_r(PHP_EOL);
        }

        // Remove setups for companies no longer assigned.
        VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $evaluation->id)
            ->whereNotIn('company_id', $assignedCompanies->lists('id'))
            ->delete();

        foreach($assignedCompanies as $assignedCompany)
        {
            $vendorWorkCategories = [];

            foreach($assignedCompany->vendors as $vendorRecord) $vendorWorkCategories[] = $vendorRecord->vendor_work_category_id;

            // check if any. Hopefully none.
            $count = VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $evaluation->id)
                ->where('company_id', '=', $assignedCompany->id)
                ->whereNotIn('vendor_work_category_id', $vendorWorkCategories)
                ->count();

            if($count > 0)
            {
                print_r("Vendor Work Category no longer assigned. Removing {$count} setups from evaluation {$evaluation->id}");
                print_r(PHP_EOL);
            }

            // Remove unrelated company setups (vendor work category changed removed).
            VendorPerformanceEvaluationSetup::where('vendor_performance_evaluation_id', '=', $evaluation->id)
                ->where('company_id', '=', $assignedCompany->id)
                ->whereNotIn('vendor_work_category_id', $vendorWorkCategories)
                ->delete();

            // Create new setups.
            foreach($vendorWorkCategories as $vendorWorkCategoryId)
            {
                VendorPerformanceEvaluationSetup::firstOrCreate([
                    'vendor_performance_evaluation_id' => $evaluation->id,
                    'company_id' => $assignedCompany->id,
                    'vendor_work_category_id' => $vendorWorkCategoryId,
                ]);
            }
        }
    }
}