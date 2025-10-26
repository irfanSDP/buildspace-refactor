<?php namespace PCK\Forms\Contracts\IndonesiaCivilContract;

use Laracasts\Validation\FormValidator;

class ArchitectInstructionResponseForm extends FormValidator {

    protected $rules = [
        'subject' => 'required|max:200',
        'content' => 'required',
    ];

}