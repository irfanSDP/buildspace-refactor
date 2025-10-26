<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class DailyLabourReportForm extends FormValidator{


	protected $rules = 
    [
        'date'                 => 'required',
        'weather'              => 'required',
        'locationLevel_0'      => 'required',
        'trade'                => 'required',
        'contractor'           => 'required',
        'work_description'     => 'required',
        'remark'               => 'required'

    ];

    protected $messages = 
    [
        'locationLevel_0.required' => 'The location field is required.'
    ];

}