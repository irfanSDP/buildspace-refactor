<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class PasswordUpdateForm extends FormValidator {

    public function getValidationRules()
    {
        return array(
            'password' => getenv('CUMBERSOME_PASSWORDS') ? 'min:6|confirmed|case_diff|numbers|letters|symbols' : 'min:6|confirmed',
        );
    }

}