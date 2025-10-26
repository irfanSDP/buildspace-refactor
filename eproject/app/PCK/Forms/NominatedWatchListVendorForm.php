<?php namespace PCK\Forms;

class NominatedWatchListVendorForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['deliberated_score'] = 'required|numeric|min:0|max:100';
    }
}