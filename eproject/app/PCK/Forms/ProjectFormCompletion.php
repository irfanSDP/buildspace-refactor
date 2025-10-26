<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ProjectFormCompletion extends FormValidator {

    /**
     * Validation rules for updating Project completion date
     *
     * @var array
     */
    protected $rules = [
        'completion_date' => 'required|date'
    ];

}