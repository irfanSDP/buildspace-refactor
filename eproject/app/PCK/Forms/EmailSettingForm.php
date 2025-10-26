<?php namespace PCK\Forms;

use PCK\Forms\CustomFormValidator;

class EmailSettingForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['footer_logo_image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        $this->rules['company_logo_alignment_identifier'] = 'required|integer';
        $this->rules['resize_footer_image'] = 'required|integer';

        if(array_key_exists('resize_footer_image', $formData) && $formData['resize_footer_image'] == 1)
        {
            $this->rules['footer_logo_width'] = 'required|integer|min:16';
            $this->rules['footer_logo_height'] = 'required|integer|min:16';
        }
    }
}