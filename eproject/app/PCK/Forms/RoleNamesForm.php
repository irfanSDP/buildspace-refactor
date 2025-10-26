<?php namespace PCK\Forms;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\Helpers\Arrays;
use PCK\Projects\Project;

class RoleNamesForm extends FormValidator {

    protected $rules = [];

    public function validate($formData)
    {
        parent::validate($formData);

        $this->customValidation($formData);
    }

    protected function customValidation($formData)
    {
        $errorMessages = $this->validateGroupNames($formData['group_names']);

        if( ! $errorMessages->isEmpty() )
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }

    private function validateGroupNames($groupNames)
    {
        $errorMessages = new MessageBag();

        foreach($groupNames as $group => $name)
        {
            $maxChars = 100;

            if( empty( $name ) ) $errorMessages->add("group_names." . $group, "The name is required");

            if( strlen($name) > $maxChars ) $errorMessages->add("group_names." . $group, "The name is too long (" . strlen($name) . "), it should not exceed " . $maxChars . " characters");

            if( Arrays::isDuplicate($groupNames, $group) ) $errorMessages->add("group_names." . $group, "The name should be unique");
        }

        return $errorMessages;
    }
}