<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class TendererRateForm extends FormValidator {

    protected $rules = [
        'rates' => 'required|mimes:zip',
    ];

    protected $messages = [
        'rates.required' => 'Please select a file to upload',
        'rates.mimes' => 'The uploaded file must be a \'.' . \PCK\Helpers\Files::EXTENSION_RATES . '\' file',
    ];

    public function validate($formData)
    {
        $validator = \Validator::make($formData, $this->rules, $this->messages);

        if ($validator->fails()) {
            throw new \Laracasts\Validation\FormValidationException('Validation failed', $validator->errors());
        }

        return true;
    }
}