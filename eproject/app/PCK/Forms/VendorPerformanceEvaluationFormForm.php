<?php namespace PCK\Forms;

use PCK\VendorPerformanceEvaluation\TemplateForm;

class VendorPerformanceEvaluationFormForm extends CustomFormValidator {
    public $templateForm;

    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:250';
        $this->rules['contract_group_category_id'] = 'required|integer';

        $this->messages['contract_group_category_id.required'] = trans('vendorManagement.vendorGroupRequired');
    }
}