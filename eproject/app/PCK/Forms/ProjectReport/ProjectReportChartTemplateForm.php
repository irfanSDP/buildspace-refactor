<?php namespace PCK\Forms\ProjectReport;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;

class ProjectReportChartTemplateForm extends FormValidator
{
    protected $rules = array(
        'reportTypeMapping' => 'required',
        'chartType' => 'required',
        'title' => 'required',
    );

    protected $messages = array();
}