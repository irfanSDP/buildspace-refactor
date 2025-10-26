<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class RiskRegisterRiskForm extends FormValidator {

    protected $rules = [
        'content'          => 'required|max:200',
        'reply_deadline'   => 'required|date',
        'probability'      => 'required|numeric|min:0|max:100',
        'impact'           => 'required|min:1',
        'detectability'    => 'required|min:1',
        'importance'       => 'required|min:1',
        'category'         => 'required|max:200',
        'trigger_event'    => 'required|max:200',
        'risk_response'    => 'required|max:200',
        'contingency_plan' => 'required|max:200',
        'status'           => 'required|min:1',
        'contract_groups'  => 'required|array|arrayNotEmpty',
        'verifiers'        => 'array',
    ];

    protected $messages = [
        'contract_groups.required' => 'Please select at least one party to request information from.',
    ];

}