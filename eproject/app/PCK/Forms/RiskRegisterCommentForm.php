<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class RiskRegisterCommentForm extends FormValidator {

    protected $rules = [
        'content'   => 'required|max:200',
        'verifiers' => 'array',
    ];

}