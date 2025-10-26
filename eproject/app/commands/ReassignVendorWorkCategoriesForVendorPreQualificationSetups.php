<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPreQualification\Setup;
use PCK\Base\Helpers;

class ReassignVendorWorkCategoriesForVendorPreQualificationSetups extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vendor-management:reassign-vendor-work-categories-for-vendor-pre-qualification-setups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassigns vendor work categories under the wrong vendor categories for tables for vendor pre qualification setups.';

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
        $this->reassign();
    }

    protected $isRequired = [];

    public function reassign()
    {
        $records = \DB::select(\DB::raw("
            select setups.vendor_category_id, setups.vendor_work_category_id, setups.pre_qualification_required, vwc.name
            from vendor_pre_qualification_setups setups
            join vendor_work_categories vwc on vwc.id = setups.vendor_work_category_id
        "));

        foreach($records as $record)
        {
            $this->isRequired[$record->name] = $record->pre_qualification_required;
        }

        $records = \DB::select(\DB::raw("
            select t.vendor_category_id, t.vendor_work_category_id, vwc.name
            from vendor_category_vendor_work_category t
            join vendor_work_categories vwc on vwc.id = t.vendor_work_category_id
            where not exists (
                select 1
                from vendor_pre_qualification_setups setups
                where setups.vendor_category_id = t.vendor_category_id 
                and setups.vendor_work_category_id = t.vendor_work_category_id 
            );
        "));

        foreach($records as $record)
        {
            Setup::create([
                'vendor_category_id' => $record->vendor_category_id,
                'vendor_work_category_id' => $record->vendor_work_category_id,
                'pre_qualification_required' => $this->isRequired[$record->name] ?? true
            ]);
        }
    }
}
