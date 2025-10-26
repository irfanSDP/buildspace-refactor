<?php

use Illuminate\Console\Command;
use PCK\WeightedNode\WeightedNode;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPreQualification\VendorPreQualification;
use PCK\VendorPreQualification\TemplateForm;
use PCK\Base\Helpers;

class ReassignVendorWorkCategoriesForVendorPreQualifications extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'vendor-management:reassign-vendor-work-categories-for-vendor-pre-qualifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassigns vendor work categories under the wrong vendor categories for tables for vendor pre qualifications.';

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
        $this->cloneRecords();
    }

    protected $vendorWorkCategories = [];
    protected $companyVendorWorkCategories = [];
    protected $vendorWorkCategoryNames = [];

    protected function generateLists()
    {
        $records = \DB::select(\DB::raw("
            SELECT vwc.name, t.vendor_work_category_id, t.vendor_category_id
            FROM vendor_category_vendor_work_category t
            JOIN vendor_categories vc on vc.id = t.vendor_category_id
            JOIN vendor_work_categories vwc on vwc.id = t.vendor_work_category_id
            "));

        foreach($records as $record)
        {
            if(!array_key_exists($record->name, $this->vendorWorkCategories)) $this->vendorWorkCategories[$record->name] = [];

            if(!array_key_exists($record->vendor_category_id, $this->vendorWorkCategories[$record->name])) $this->vendorWorkCategories[$record->name][$record->vendor_category_id] = [];

            $this->vendorWorkCategories[$record->name][$record->vendor_category_id][] = $record->vendor_work_category_id;

            $this->vendorWorkCategoryNames[$record->vendor_work_category_id] = $record->name;
        }

        $records = \DB::select(\DB::raw("
            select cvc.company_id, vcvwc.vendor_work_category_id
            from vendor_category_vendor_work_category vcvwc 
            join company_vendor_category cvc on cvc.vendor_category_id = vcvwc.vendor_category_id
            "));

        foreach($records as $record)
        {
            if(!array_key_exists($record->company_id, $this->companyVendorWorkCategories)) $this->companyVendorWorkCategories[$record->company_id] = [];

            $this->companyVendorWorkCategories[$record->company_id][] = $record->vendor_work_category_id;
        }
    }

    public function cloneRecords()
    {
        $records = VendorPreQualification::with('vendorRegistration.company')->get();

        foreach($records as $record)
        {
            foreach($this->companyVendorWorkCategories[$record->vendorRegistration->company_id] ?? [] as $vendorWorkCategoryId)
            {
                // if not matching
                if($this->vendorWorkCategoryNames[$record->vendor_work_category_id] != $this->vendorWorkCategoryNames[$vendorWorkCategoryId]) continue;

                $object = VendorPreQualification::where('vendor_registration_id', '=', $record->vendorRegistration->id)
                    ->where('vendor_work_category_id', '=', $record->vendor_work_category_id)
                    ->first();

                if($object) continue;

                $this->cloneVendorPreQualification($object, $record->vendor_work_category_id);
            }
        }
    }

    protected function cloneVendorPreQualification($vendorPreQualification, $vendorWorkCategoryId)
    {
        $templateForm = TemplateForm::getTemplateForm($vendorWorkCategoryId);

        if(!$templateForm)
        {
            $originalTemplateForm = TemplateForm::find($vendorPreQualification->template_form_id);

            if($originalTemplateForm)
            {
                $templateWeightedNode = $originalTemplateForm->weightedNode->clone();
            }
            else
            {
                $templateWeightedNode = WeightedNode::create([]);
            }

            $templateForm = TemplateForm::create([
                "vendor_work_category_id" => $vendorWorkCategoryId,
                "weighted_node_id" => $templateWeightedNode->id,
                "status_id" => $originalTemplateForm->status_id,
            ]);
        }

        $weightedNode = $templateForm->weightedNode->clone();

        $clone = VendorPreQualification::create([
            'vendor_work_category_id' => $vendorWorkCategoryId,
            'created_at' => $vendorPreQualification->created_at,
            'updated_at' => $vendorPreQualification->updated_at,
            'weighted_node_id' => $weightedNode->id,
            'status_id' => $vendorPreQualification->status_id,
            "vendor_management_grade_id" => $grade ? $grade->id : null,
            'vendor_registration_id' => $vendorPreQualification->vendor_registration_id,
            'template_form_id' => $templateForm->id,
        ]);
    }
}
