<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\Vendor\Vendor;
use PCK\Base\Helpers;

class ReassignVendorWorkCategoriesForVendors extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vendor-management:reassign-vendor-work-categories-for-vendors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassigns vendor work categories under the wrong vendor categories for the vendors table.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->generateLists();
        $this->reassignVendors();
    }

    protected $vendorCategories = [];
    protected $vendorWorkCategories = [];
    protected $vendorWorkCategoryNames = [];
    protected $relevantVendorCategories = [];

    protected function generateLists()
    {
        foreach(VendorCategory::all() as $vendorCategory)
        {
            $this->vendorWorkCategories[$vendorCategory->id] = $vendorCategory->vendorWorkCategories->lists('id', 'name');
        }

        foreach(VendorWorkCategory::all() as $vendorWorkCategory)
        {
            $this->vendorWorkCategoryNames[$vendorWorkCategory->id] = $vendorWorkCategory->name;

            $this->vendorCategories[$vendorWorkCategory->id] = $vendorWorkCategory->vendorCategories->lists('id',  'name');
        }

        $companyVendorCategories = \DB::table('company_vendor_category')->get();

        foreach($companyVendorCategories as $record)
        {
            if(!array_key_exists($record->company_id, $this->relevantVendorCategories)) $this->relevantVendorCategories[$record->company_id] = [];

            $this->relevantVendorCategories[$record->company_id][] = $record->vendor_category_id;
        }
    }

    public function reassignVendors()
    {
        $records = \DB::select(\DB::raw("
            SELECT v.id, v.vendor_work_category_id, pivot.vendor_category_id, v.company_id, v.type, v.is_qualified, v.watch_list_entry_date, v.watch_list_release_date, v.created_at, v.updated_at
            FROM vendors v
            JOIN vendor_category_vendor_work_category pivot on pivot.vendor_work_category_id = v.vendor_work_category_id;
        "));

        $insertRows = [];

        foreach($records as $record)
        {
            foreach($this->relevantVendorCategories[$record->company_id] as $vendorCategoryId)
            {
                if(in_array($vendorCategoryId, $this->vendorCategories[$record->vendor_work_category_id])) continue;

                $newVendorWorkCategoryId = $this->vendorWorkCategories[$vendorCategoryId][$this->vendorWorkCategoryNames[$record->vendor_work_category_id] ?? null] ?? null;

                // No matching vendorWorkCategory under this vendorCategory
                if(is_null($newVendorWorkCategoryId)) continue;

                $insertRows[] = [$newVendorWorkCategoryId, $record->company_id, $record->type, $record->is_qualified, $record->watch_list_entry_date, $record->watch_list_release_date, $record->created_at, $record->updated_at];
            }
        }

        Helpers::arrayBatch($insertRows, 200, function($batch)
        {
            $insertRecords = [];
            $questionMarks = [];

            foreach($batch as $item)
            {
                $insertRecords = array_merge($insertRecords, $item);
                $questionMarks[] = '('.implode(',', array_fill(0, count($item), '?')).')';
            }

            if($insertRecords)
            {
                \DB::statement("INSERT INTO vendors
                    (vendor_work_category_id, company_id, type, is_qualified, watch_list_entry_date, watch_list_release_date, created_at, updated_at)
                    VALUES ".implode(',', $questionMarks), $insertRecords);
            }
        });
    }
}
