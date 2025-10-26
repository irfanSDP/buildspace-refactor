<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class TechnicalEvaluationAttachmentListItemForm extends FormValidator {

    protected $rules = [
        'description' => 'required|max: 255',
    ];

}