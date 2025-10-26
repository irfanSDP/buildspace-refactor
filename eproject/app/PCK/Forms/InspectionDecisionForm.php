<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class InspectionDecisionForm extends FormValidator {

    protected $rules = [
        'decision' => 'required|integer',
    ];
}