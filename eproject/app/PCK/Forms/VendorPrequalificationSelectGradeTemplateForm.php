<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel;

class VendorPrequalificationSelectGradeTemplateForm extends FormValidator
{
    protected $rules = [
        'grade_template' => 'required|integer|min:1',
    ];

    protected $messages = [
        'grade_template.required' => 'Please select a grade template.',
    ];
}

