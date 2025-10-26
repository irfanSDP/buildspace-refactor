<?php

use PCK\SupplierCreditFacility\SupplierCreditFacilitySetting;

class SupplierCreditFacilitySettingsController extends Controller
{
    public function edit()
    {
        $setting = SupplierCreditFacilitySetting::first();

        return View::make('vendor_management.settings.supplier_credit_facility.edit', [
            'setting' => $setting,
        ]);
    }

    public function update()
    {
        $inputs = Input::all();

        $setting = SupplierCreditFacilitySetting::first();
        $setting->has_attachments = isset($inputs['has_attachments']);
        $setting->save();

        Flash::success(trans('forms.saved'));

        return Redirect::back();
    }
}