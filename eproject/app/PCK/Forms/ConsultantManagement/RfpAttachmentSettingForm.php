<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class RfpAttachmentSettingForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['title'] = 'required|min:1|max:250|unique:consultant_management_rfp_attachment_settings,title,'.$formData['id'].',id,vendor_category_rfp_id,'.$formData['vendor_category_rfp_id'];
        $this->rules['mandatory'] = 'required';
    }
}
