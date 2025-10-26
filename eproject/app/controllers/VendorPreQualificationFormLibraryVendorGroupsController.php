<?php

use PCK\ContractGroupCategory\ContractGroupCategory;

class VendorPreQualificationFormLibraryVendorGroupsController extends \BaseController {

    public function index()
    {
        $data = [];

        $records = ContractGroupCategory::orderBy('name', 'asc')
            ->where('hidden', '=', false)
            ->where('type', '=', ContractGroupCategory::TYPE_EXTERNAL)
            ->get();

        foreach($records as $record)
        {
            $data[] = [
                'id'                   => $record->id,
                'name'                 => $record->name,
                'route:workCategories' => route('vendorPreQualification.formLibrary.vendorWorkCategories.index', array($record->id)),
            ];
        }

        $data[] = ['name' => ''];

        return View::make('vendor_pre_qualification.form_library_vendor_groups', compact('data'));
    }
}
