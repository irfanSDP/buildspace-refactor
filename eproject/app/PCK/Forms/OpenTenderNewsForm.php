<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\OpenTenderNews\OpenTenderNews;
use PCK\Exceptions\ValidationException;

class OpenTenderNewsForm extends FormValidator{

    protected $rules = [
        'start_time' => 'required|date',
        'end_time' => 'required|date|after:start_time',
        'description' => 'required',
    ];

    protected $messages = [
        'start_time.required' => 'The start time is required.',
        'start_time.date' => 'The start time must be a valid date.',
        'end_time.required' => 'The end time is required.',
        'end_time.date' => 'The end time must be a valid date.',
        'end_time.after' => 'The end time must be after the start time.',
        'description.required' => 'The description is required.',
    ];

    public function validate($formData)
    {
    	parent::validate($formData);
    }

}