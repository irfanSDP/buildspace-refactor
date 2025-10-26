<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class InspectionReadyDateAndTimeForm extends FormValidator {

    protected $rules = [
        'ready_for_inspection_date' => 'required|date',
    ];
}