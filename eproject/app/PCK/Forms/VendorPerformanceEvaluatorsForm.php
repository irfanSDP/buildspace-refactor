<?php namespace PCK\Forms;

class VendorPerformanceEvaluatorsForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['evaluator_ids'] = 'required|array|min:1';

        $this->messages['evaluator_ids.required'] = trans('vendorManagement.evaluatorRequiredErrorMessage');
    }
}