<?php namespace PCK\Forms\ProjectReport;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class ProjectReportChartPlotTemplateForm extends FormValidator
{
    protected $rules = array(
        //'title' => 'required',
    );

    protected $messages = array(
        //'title.required' => 'Template title is required.',
    );
}