<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class UpdateMyProfileForm extends FormValidator {

    public function getValidationRules()
    {
        return array(
            'name'           => 'required|min:4',
            'contact_number' => 'required',
            'password'       => getenv('CUMBERSOME_PASSWORDS') ? 'min:6|confirmed|case_diff|numbers|letters|symbols' : 'min:6|confirmed',
        );
    }

}