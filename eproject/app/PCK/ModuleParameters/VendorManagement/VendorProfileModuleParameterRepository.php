<?php namespace PCK\ModuleParameters\VendorManagement;

class VendorProfileModuleParameterRepository
{
    public function update($inputs)
    {
        $record = VendorProfileModuleParameter::first();

        $record->registration_price = $inputs['registration_price'];
        $record->renewal_price      = $inputs['renewal_price'];

        $record->validity_period_of_active_vendor_in_avl_value = $inputs['validity_period_of_active_vendor_in_avl_value'];
        $record->validity_period_of_active_vendor_in_avl_unit  = $inputs['validity_period_of_active_vendor_in_avl_unit'];

        $record->grace_period_of_expired_vendor_before_moving_to_dvl_value = $inputs['grace_period_of_expired_vendor_before_moving_to_dvl_value'];
        $record->grace_period_of_expired_vendor_before_moving_to_dvl_unit  = $inputs['grace_period_of_expired_vendor_before_moving_to_dvl_unit'];

        $record->vendor_retain_period_in_wl_value = $inputs['vendor_retain_period_in_wl_value'];
        $record->vendor_retain_period_in_wl_unit  = $inputs['vendor_retain_period_in_wl_unit'];

        $record->renewal_period_before_expiry_in_days = $inputs['renewal_period_before_expiry_in_days'];

        $record->watch_list_nomineee_to_active_vendor_list_threshold_score = $inputs['watch_list_nomineee_to_active_vendor_list_threshold_score'];
        $record->watch_list_nomineee_to_watch_list_threshold_score         = $inputs['watch_list_nomineee_to_watch_list_threshold_score'];

        $record->save();

        return VendorProfileModuleParameter::find($record->id);
    }
}