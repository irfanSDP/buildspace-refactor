<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class WeightedNodeForm extends CustomFormValidator {

    protected $throwException = false;

    protected $rules = [
        'name'   => 'required|max:250',
        'weight' => 'required|numeric|min:0|max:100',
    ];

}
