<?php namespace PCK\ModuleParameters\VendorManagement;

class VendorPerformanceEvaluationModuleParameterRepository
{
    public function update($inputs)
    {
        $record = VendorPerformanceEvaluationModuleParameter::first();

        $record->default_time_frame_for_vpe_cycle_value = $inputs['default_time_frame_for_vpe_cycle_value'];
        $record->default_time_frame_for_vpe_cycle_unit  = $inputs['default_time_frame_for_vpe_cycle_unit'];

        $record->default_time_frame_for_vpe_submission_value = $inputs['default_time_frame_for_vpe_submission_value'];
        $record->default_time_frame_for_vpe_submission_unit  = $inputs['default_time_frame_for_vpe_submission_unit'];

        $record->attachments_required                 = $inputs['attachments_required'] ?? false;
        $record->attachments_required_score_threshold = $inputs['attachments_required_score_threshold'];

        $record->passing_score = $inputs['passing_score'];

        $record->vendor_management_grade_id = !empty($inputs['vendor_management_grade_id']) ? $inputs['vendor_management_grade_id'] : null;

        $record->email_reminder_before_cycle_end_date       = isset($inputs['email_reminder_before_cycle_end_date']);
        $record->email_reminder_before_cycle_end_date_value = $inputs['email_reminder_before_cycle_end_date_value'];
        $record->email_reminder_before_cycle_end_date_unit  = $inputs['email_reminder_before_cycle_end_date_unit'];

        $record->save();

        $newList = $inputs['number_of_days_ahead_of_submission'] ?? [];

        $currentReminders = VendorPerformanceEvaluationSubmissionReminderSetting::lists('number_of_days_before');

        $remindersToDelete = array_diff($currentReminders, $newList);
        $remindersToAdd    = array_diff($newList, $currentReminders);

        VendorPerformanceEvaluationSubmissionReminderSetting::whereIn('number_of_days_before', $remindersToDelete)->delete();

        foreach($remindersToAdd as $numberOfDays)
        {
            VendorPerformanceEvaluationSubmissionReminderSetting::create(array(
                'number_of_days_before' => $numberOfDays
            ));
        }

        return VendorPerformanceEvaluationModuleParameter::find($record->id);
    }
}