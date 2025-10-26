<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use PCK\VendorRegistration\VendorRegistration;
use PCK\VendorManagement\VendorManagementUserPermission;

class VendorRegistrationAssignProcessorForm extends CustomFormValidator {

    protected function preParentValidation($formData)
    {
        $errors = $this->getNewMessageBag();

        $assignableStatuses = [VendorRegistration::STATUS_SUBMITTED, VendorRegistration::STATUS_PROCESSING];

        $vendorRegistration = VendorRegistration::find($formData['vendor_registration_id']);

        if(!in_array($vendorRegistration->status, $assignableStatuses))
        {
            $errors->add('form', trans('vendorManagement.error:processCannotBeAssigned'));
        }

        if(!in_array($formData['processor_id'], VendorManagementUserPermission::getPermissionUsers()[VendorManagementUserPermission::TYPE_APPROVAL_REGISTRATION]))
        {
            $errors->add('processor_id', trans('vendorManagement.error:notProcessor'));
        }

        return $errors;
    }
}