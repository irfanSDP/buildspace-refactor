<?php namespace PCK\DigitalStar\ModuleParameters;

class DsModuleParameterRepository
{
    public function update($inputs)
    {
        $record = DsModuleParameter::first();

        $record->vendor_management_grade_id = !empty($inputs['vendor_management_grade_id']) ? $inputs['vendor_management_grade_id'] : null;

        $record->email_reminder_before_cycle_end_date       = isset($inputs['email_reminder_before_cycle_end_date']);
        $record->email_reminder_before_cycle_end_date_value = $inputs['email_reminder_before_cycle_end_date_value'];
        $record->email_reminder_before_cycle_end_date_unit  = $inputs['email_reminder_before_cycle_end_date_unit'];

        $record->save();

        /*$newList = $inputs['number_of_days_ahead_of_submission'] ?? [];

        $currentReminders = DsSubmissionReminderSetting::lists('number_of_days_before');

        $remindersToDelete = array_diff($currentReminders, $newList);
        $remindersToAdd    = array_diff($newList, $currentReminders);

        DsSubmissionReminderSetting::where('ds_module_parameter_id', $record->id)->whereIn('number_of_days_before', $remindersToDelete)->delete();

        foreach($remindersToAdd as $numberOfDays)
        {
            DsSubmissionReminderSetting::create(array(
                'number_of_days_before' => $numberOfDays
            ));
        }*/

        return DsModuleParameter::find($record->id);
    }
}