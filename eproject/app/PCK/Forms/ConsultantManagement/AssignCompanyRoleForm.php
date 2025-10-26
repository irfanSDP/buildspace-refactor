<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class AssignCompanyRoleForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['company_id'] = 'required|exists:companies,id';
    }
}
