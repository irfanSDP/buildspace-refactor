<?php

use PCK\GeneralSettings\GeneralSetting;

class GeneralSettingController extends Controller
{

	public function index()
    {
        $generalSetting = GeneralSetting::first();
        if (! $generalSetting) {
            $generalSetting = new GeneralSetting();
            $generalSetting->save();
        }
        return View::make('general_settings.index', compact('generalSetting'));
    }

    public function store()
    {
        $input = Input::all();

        $generalSetting = GeneralSetting::first() ?? new GeneralSetting();

        if (isset($input['view_subsidiary'])) {
            $generalSetting->view_own_created_subsidiary = $input['view_subsidiary'] == 1 ? true : false;
        }

        if (isset($input['view_tenders'])) {
            $generalSetting->view_tenders = $input['view_tenders'] == 1 ? true : false;
        }

        if (isset($input['enable_e_bidding'])) {
            $generalSetting->enable_e_bidding = $input['enable_e_bidding'] == 1 ? true : false;
        }

        $generalSetting->save();

        return Response::json([
            'success' => true,
            'message' => trans('forms.updateSuccessful'),
        ]);
    }

}
