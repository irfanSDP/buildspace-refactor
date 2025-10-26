<?php

use PCK\VendorDetailSetting\VendorDetailSetting;
use PCK\VendorDetailSetting\VendorDetailAttachmentSetting;
use PCK\VendorManagement\InstructionSetting;

class VendorDetailSettingsController extends \BaseController {

    public function edit()
    {
        $settings           = VendorDetailSetting::first();
        $attachmentSettings = VendorDetailAttachmentSetting::first();
        $instructions       = InstructionSetting::first();

        return View::make('vendor_management.settings.vendor_details.edit', compact('settings', 'attachmentSettings', 'instructions'));
    }

    public function update()
    {
        VendorDetailSetting::first()->update(Input::all());

        \Flash::success(trans('forms.saved'));

        return Redirect::back();
    }

    public function attachmentSettingsUpdate()
    {
        $inputs = Input::all();

        VendorDetailAttachmentSetting::updateSetting($inputs);

        \Flash::success(trans('forms.saved'));

        return Redirect::back();
    }

    public function sectionInstructionsUpdate()
    {
        InstructionSetting::first()->update(Input::all());

        \Flash::success(trans('forms.saved'));

        return Redirect::back();
    }
}