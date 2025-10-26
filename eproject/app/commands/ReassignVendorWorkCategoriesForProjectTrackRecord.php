<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\TrackRecordProject\TrackRecordProject;
use PCK\TrackRecordProject\TrackRecordProjectVendorWorkSubcategory;
use PCK\Base\Helpers;

class ReassignVendorWorkCategoriesForProjectTrackRecord extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vendor-management:reassign-vendor-work-categories-for-project-track-record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassigns vendor work categories under the wrong vendor categories for tables for project track record.';

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
        $this->reassignWorkCategories();
        $this->reassignWorkSubCategories();
    }

    protected $vendorCategories = [];
    protected $vendorWorkCategories = [];
    protected $vendorWorkCategoryNames = [];

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
    }

    /*
    We check if the vendor category and the vendor work category is matching (i.e. linked).
    If they are not matching, that should mean that a new vendor work category has been created and we should now use the new one.
    */
    public function reassignWorkCategories()
    {
        foreach(TrackRecordProject::all() as $projectTrackRecord)
        {
            if(in_array($projectTrackRecord->vendor_work_category_id, $this->vendorWorkCategories[$projectTrackRecord->vendor_category_id])) continue;

            $newVendorWorkCategoryId = $this->vendorWorkCategories[$projectTrackRecord->vendor_category_id][$this->vendorWorkCategoryNames[$projectTrackRecord->vendor_work_category_id] ?? null] ?? null;

            if(is_null($newVendorWorkCategoryId))
            {
                $this->error("Failed update [id:{$projectTrackRecord->id}]. {$this->vendorWorkCategoryNames[$projectTrackRecord->vendor_work_category_id]} does not exist under vendor category {$projectTrackRecord->vendor_category_id}");

                continue;
            }

            \DB::statement("UPDATE track_record_projects SET vendor_work_category_id = ? WHERE id = ?", [$newVendorWorkCategoryId, $projectTrackRecord->id]);
        }
    }

    public function reassignWorkSubCategories()
    {
        $records = \DB::select(\DB::raw("
            select
            case 
                when exists_table.id is null then false
                else true
            end as actual_record_exists,
            m.id,
            actual_vws.id as actual_id
            from track_record_project_vendor_work_subcategories m
            join track_record_projects trp on trp.id = m.track_record_project_id
            join vendor_work_subcategories vws on vws.id = m.vendor_work_subcategory_id
            left join vendor_work_subcategories actual_vws on actual_vws.vendor_work_category_id = trp.vendor_work_category_id and actual_vws.name = vws.name
            left join track_record_project_vendor_work_subcategories exists_table on exists_table.track_record_project_id = m.track_record_project_id and exists_table.vendor_work_subcategory_id = actual_vws.id
            where true
            and trp.vendor_work_category_id != vws.vendor_work_category_id;
        "));

        foreach($records as $record)
        {
            if(!is_null($record->actual_id) && !$record->actual_record_exists)
            {
                \DB::statement("UPDATE track_record_project_vendor_work_subcategories t1
                    SET vendor_work_subcategory_id = ?
                    WHERE id = ?
                    AND NOT EXISTS (
                        SELECT 1 FROM track_record_project_vendor_work_subcategories t2 
                        WHERE t2.track_record_project_id = t1.track_record_project_id
                        AND t2.vendor_work_subcategory_id = ?
                    )", [$record->actual_id, $record->id, $record->actual_id]);
            }
            else
            {
                \DB::statement("DELETE from track_record_project_vendor_work_subcategories WHERE id = ?", [$record->id]);
            }
        }
    }
}
