<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class RequestForInspectionInspectionForm extends FormValidator {

    protected $rules = [
        'comments'     => 'required|max:200',
        'remarks'      => 'required|max:200',
        'inspected_at' => 'required|date',
        'status'       => 'required|integer',
        'verifiers'    => 'array',
    ];

    protected $messages = [
        'contract_groups.required' => 'Please select at least one party to request information from.',
    ];

}