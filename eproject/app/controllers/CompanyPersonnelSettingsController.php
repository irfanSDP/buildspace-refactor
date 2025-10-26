<?php

use PCK\CompanyPersonnel\CompanyPersonnelSetting;

class CompanyPersonnelSettingsController extends Controller
{
    public function edit()
    {
        $setting = CompanyPersonnelSetting::first();

        return View::make('vendor_management.settings.company_personnel.edit', [
            'setting' => $setting,
        ]);
    }

    public function update()
    {
        $inputs = Input::all();

        $setting                  = CompanyPersonnelSetting::first();
        $setting->has_attachments = isset($inputs['has_attachments']);
        $setting->save();

        Flash::success(trans('forms.saved'));

        return Redirect::back();
    }
}