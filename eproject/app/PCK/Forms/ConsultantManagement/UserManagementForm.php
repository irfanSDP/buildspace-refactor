<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;
use PCK\ConsultantManagement\ConsultantManagementContract;

class UserManagementForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        if(!array_key_exists('editor', $formData))
        {
            $prefix = null;
            switch($formData['role'])
            {
                case ConsultantManagementContract::ROLE_RECOMMENDATION_OF_CONSULTANT:
                    $prefix = 'roc';
                    break;
                case ConsultantManagementContract::ROLE_LIST_OF_CONSULTANT:
                    $prefix = 'loc';
                    break;
                default:
                    throw new \Exception('Invalid role');
            }

            $this->rules[$prefix.'_empty_editor'] = 'required';
            $this->messages[$prefix.'_empty_editor.required'] = 'At least one User as Editor is required';
        }

    }
}