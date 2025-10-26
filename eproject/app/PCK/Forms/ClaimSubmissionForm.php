<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ClaimSubmissionForm extends FormValidator {

    protected $rules = [
        'claims' => 'required|mimes:zip',
    ];

}