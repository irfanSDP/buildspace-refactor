<?php namespace PCK\Forms\ProjectReport;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class ProjectReportTemplateForm extends FormValidator
{
    protected $rules = [
        'title' => 'required',
    ];

    protected $messages = [
        'title.required' => 'Template title is required.',
    ];
}