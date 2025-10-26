<?php namespace PCK\Forms;

use PCK\Vendor\Vendor;
use PCK\TrackRecordProject\TrackRecordProject;

class VendorForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['vendor.vendor_category_id'] = 'required|integer|exists:vendor_categories,id';
        $this->messages['vendor.vendor_category_id.required'] = trans('vendorManagement.vendorCategory').' is required';

        $this->rules['vendor.vendor_work_category_id'] = 'required|integer|exists:vendor_work_categories,id|unique:vendors,vendor_work_category_id,'.$formData['id'].',id,company_id,'.$formData['cid'];
        $this->messages['vendor.vendor_work_category_id.required'] = trans('vendorManagement.vendorWorkCategory').' is required';
        $this->messages['vendor.vendor_work_category_id.unique'] = 'The selected '.trans('vendorManagement.vendorWorkCategory').' is already in the records';

        $this->rules['vendor.type'] = 'required|integer';
        $this->messages['vendor.type.required'] = trans('general.type').' is required';

        if(array_key_exists('type', $formData['vendor']) && $formData['vendor']['type'] == Vendor::TYPE_WATCH_LIST)
        {
            $this->rules['vendor.watch_list_entry_date'] = 'required|date';
            $this->messages['vendor.watch_list_entry_date.required'] = trans('vendorManagement.entryDate').' is required';

            $this->rules['vendor.watch_list_release_date'] = 'required|date|after:vendor.watch_list_entry_date';
            $this->messages['vendor.watch_list_release_date.required'] = trans('vendorManagement.releaseDate').' is required';
            $this->messages['vendor.watch_list_release_date.after'] = trans('vendorManagement.releaseDate').' must be after '.trans('vendorManagement.entryDate');
        }
    }

    protected function postParentValidation($formData)
    {
        $errors = $this->getNewMessageBag();

        if($vendor = Vendor::find($formData['id']))
        {
            $projectTrackRecordVendorWorkCategories = TrackRecordProject::where('vendor_registration_id', '=', $vendor->company->finalVendorRegistration->id)->lists('vendor_work_category_id');

            if($vendor->vendor_work_category_id != $formData['vendor']['vendor_work_category_id'] && in_array($vendor->vendor_work_category_id, $projectTrackRecordVendorWorkCategories))
            {
                $errors->add('vendor.vendor_work_category_id', 'Unable to change Vendor Work Category. This Vendor Work Category is listed in the Project Track Records.');
            }
        }

        return $errors;
    }
}