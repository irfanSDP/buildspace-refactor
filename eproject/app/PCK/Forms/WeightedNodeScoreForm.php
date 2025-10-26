<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class WeightedNodeScoreForm extends CustomFormValidator {

    protected $throwException = false;

    protected $rules = [
        'name'   => 'required|max:250',
        'value'  => 'required|numeric|min:0|max:100',
    ];

}
