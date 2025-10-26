<?php

use PCK\LoginRequestFormSetting\LoginRequestFormSetting;

class LoginRequestFormSettingsController extends Controller
{
    public function edit()
    {
        $settings = LoginRequestFormSetting::first();

        return View::make('login_request_form.edit', compact('settings'));
    }

    public function update()
    {
        $input = Input::all();

        $input['include_disclaimer']   = Input::get('include_disclaimer') ?? false;
        $input['include_instructions'] = Input::get('include_instructions') ?? false;

        unset($input['_token']);

        LoginRequestFormSetting::first()->update($input);

        \Flash::success(trans('vendorManagement.formSavedSuccessfully'));

        return Redirect::back();
    }
}