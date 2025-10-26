<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ForumThreadForm extends FormValidator {

    protected $rules = [
        'title' => 'required|max:200',
    ];

}