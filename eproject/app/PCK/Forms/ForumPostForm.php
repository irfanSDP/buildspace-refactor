<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ForumPostForm extends FormValidator {

    protected $rules = [
        'content' => 'required',
    ];

}