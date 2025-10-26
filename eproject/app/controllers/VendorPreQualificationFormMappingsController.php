<?php

use PCK\ContractGroupCategory\ContractGroupCategory;
use PCK\VendorCategory\VendorCategory;
use PCK\VendorWorkCategory\VendorWorkCategory;
use PCK\VendorPreQualification\TemplateForm;
use PCK\VendorPreQualification\Setup;

class VendorPreQualificationFormMappingsController extends \BaseController {

    public function index()
    {
        $data = array();

        $vendorCategories = VendorCategory::where('hidden', '=', false)->orderBy('name', 'asc')->get();

        foreach($vendorCategories as $vendorCategory)
        {
            foreach($vendorCategory->vendorWorkCategories as $vendorWorkCategory)
            {
                $setup = Setup::firstOrNew(array(
                    'vendor_category_id' => $vendorCategory->id,
                    'vendor_work_category_id' => $vendorWorkCategory->id
                ));

                $editRouteParams = isset($setup->id)
                    ? array('setupId' => $setup->id)
                    : array('vendorCategoryId' => $vendorCategory->id, 'vendorWorkCategoryId' => $vendorWorkCategory->id);

                $templateForm = TemplateForm::getTemplateForm($vendorWorkCategory->id);

                $data[] = array(
                    'id'                       => $setup->id,
                    'vendorCategory'           => $vendorCategory->name,
                    'vendorWorkCategory'       => $vendorWorkCategory->name,
                    'form'                     => ! is_null($templateForm) ? $templateForm->weightedNode->name : null,
                    'preQualificationRequired' => $setup->pre_qualification_required,
                    'route:edit'               => route('vendorPreQualification.formMapping.edit', $editRouteParams),
                );
            }
        }

        return View::make('vendor_management.form_mapping.vendor_pre_qualification', compact('data'));
    }

    public function edit()
    {
        $setupId = Input::get('setupId'); // Retrieve the setupId from query parameters

        if ($setupId) {
            // Edit existing record
            $setup = Setup::find($setupId);
            if (! $setup) {
                return Redirect::back();
                //\Flash::error(trans(''));
            }
            $vendorCategory = $setup->vendorCategory;
            $vendorWorkCategory = $setup->vendorWorkCategory;
        } else {
            // Create new record
            $vendorCategory = VendorCategory::find(Input::get('vendorCategoryId'));
            $vendorWorkCategory = VendorWorkCategory::find(Input::get('vendorWorkCategoryId'));

            if (! $vendorCategory || ! $vendorWorkCategory) {
                return Redirect::back();
            }

            $setup = new Setup();
        }

        $templateForm = TemplateForm::getTemplateForm($vendorWorkCategory->id);

        if (! is_null($templateForm)) {
            $templateFormName = $templateForm->weightedNode->name;
        } else {
            $templateFormName = '('.trans('vendorManagement.noTemplateFormAvailable').')';
        }

        return View::make('vendor_management.form_mapping.vendor_pre_qualification_edit', compact('vendorCategory', 'vendorWorkCategory', 'setup', 'templateFormName'));
    }

    public function update()
    {
        $setupId = Input::get('setupId'); // Retrieve the setupId from query parameters

        if ($setupId) {
            // Edit existing record
            $setup = Setup::find($setupId);
            if (! $setup) {
                return Redirect::back();
            }
        } else {
            // Create new record
            $vendorCategory = VendorCategory::find(Input::get('vendorCategoryId'));
            $vendorWorkCategory = VendorWorkCategory::find(Input::get('vendorWorkCategoryId'));

            if (! $vendorCategory || ! $vendorWorkCategory) {
                return Redirect::back();
            }

            $setup = new Setup();
            $setup->vendor_category_id = $vendorCategory->id;
            $setup->vendor_work_category_id = $vendorWorkCategory->id;
        }

        $setup->pre_qualification_required = Input::has('pre_qualification_required');

        $setup->save();

        return Redirect::route('vendorPreQualification.formMapping');
    }

}