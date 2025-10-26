<?php

use PCK\TrackRecordProject\ProjectTrackRecordSetting;

class ProjectTrackRecordSettingsController extends Controller
{
    public function edit()
    {
        $setting = ProjectTrackRecordSetting::first();
        
        return View::make('vendor_management.settings.project_track_record.edit', [
            'setting' => $setting,
        ]);
    }

    public function update()
    {
        $inputs = Input::all();

        $setting                                          = ProjectTrackRecordSetting::first();
        $setting->project_detail_attachments              = isset($inputs['project_detail_attachments']);
        $setting->project_quality_achievement_attachments = isset($inputs['project_quality_achievement_attachments']);
        $setting->project_award_recognition_attachments   = isset($inputs['project_award_recognition_attachments']);
        $setting->save();

        Flash::success(trans('forms.saved'));

        return Redirect::back();
    }
}