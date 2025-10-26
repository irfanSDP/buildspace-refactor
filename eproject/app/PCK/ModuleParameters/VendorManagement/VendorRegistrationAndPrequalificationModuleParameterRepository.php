<?php namespace PCK\ModuleParameters\VendorManagement;

class VendorRegistrationAndPrequalificationModuleParameterRepository
{
    public function update($inputs)
    {
        $record = VendorRegistrationAndPrequalificationModuleParameter::first();

        $record->valid_period_of_temp_login_acc_to_unreg_vendor_value = $inputs['valid_period_of_temp_login_acc_to_unreg_vendor_value'];
        $record->valid_period_of_temp_login_acc_to_unreg_vendor_unit  = $inputs['valid_period_of_temp_login_acc_to_unreg_vendor_unit'];

        $record->allow_only_one_comp_to_reg_under_multi_vendor_category = $inputs['allow_only_one_comp_to_reg_under_multi_vendor_category'];

        $record->vendor_reg_cert_generated_sent_to_successful_reg_vendor = $inputs['vendor_reg_cert_generated_sent_to_successful_reg_vendor'];

        $record->notify_vendor_before_end_of_temp_acc_valid_period_value = $inputs['notify_vendor_before_end_of_temp_acc_valid_period_value'];
        $record->notify_vendor_before_end_of_temp_acc_valid_period_unit  = $inputs['notify_vendor_before_end_of_temp_acc_valid_period_unit'];

        $record->period_retain_unsuccessful_reg_and_preq_submission_value         = $inputs['period_retain_unsuccessful_reg_and_preq_submission_value'];
        $record->period_retain_unsuccessful_reg_and_preq_submission_unit          = $inputs['period_retain_unsuccessful_reg_and_preq_submission_unit'];
        $record->start_period_retain_unsuccessful_reg_and_preq_submission_value   = $inputs['start_period_retain_unsuccessful_reg_and_preq_submission_value'];

        $record->notify_purge_data_before_end_period_for_unsuccessful_sub_value = $inputs['notify_purge_data_before_end_period_for_unsuccessful_sub_value'];
        $record->notify_purge_data_before_end_period_for_unsuccessful_sub_unit  = $inputs['notify_purge_data_before_end_period_for_unsuccessful_sub_unit'];
        $record->vendor_declaration                                             = $inputs['vendor_declaration'];

        $record->valid_submission_days = $inputs['valid_submission_days'];

        $record->retain_info_of_unsuccessfully_reg_vendor_after_data_purge = ($inputs['retain_info_of_unsuccessfully_reg_vendor_after_data_purge'] == VendorRegistrationAndPrequalificationModuleParameter::OPTION_YES) ? true : false;

        if($record->retain_info_of_unsuccessfully_reg_vendor_after_data_purge)
        {
            $record->retain_company_name         = isset($inputs['retain_company_name']);
            $record->retain_roc_number           = isset($inputs['retain_roc_number']);
            $record->retain_email                = isset($inputs['retain_email']);
            $record->retain_contact_number       = isset($inputs['retain_contact_number']);
            $record->retain_date_of_data_purging = isset($inputs['retain_date_of_data_purging']);
        }
        else
        {
            $record->retain_company_name         = false;
            $record->retain_roc_number           = false;
            $record->retain_email                = false;
            $record->retain_contact_number       = false;
            $record->retain_date_of_data_purging = false;
        }

        $record->notify_vendors_for_renewal_value = $inputs['notify_vendors_for_renewal_value'];
        $record->notify_vendors_for_renewal_unit  = $inputs['notify_vendors_for_renewal_unit'];
 
        $record->vendor_management_grade_id = $inputs['vendor_management_grade_id'] ?? null;

        $record->save();

        return VendorRegistrationAndPrequalificationModuleParameter::find($record->id);
    }
}