<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class AttachmentSettingForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['title'] = 'required|min:1|max:250|unique:consultant_management_attachment_settings,title,'.$formData['id'].',id,consultant_management_contract_id,'.$formData['consultant_management_contract_id'];
        $this->rules['mandatory'] = 'required';
    }
}
