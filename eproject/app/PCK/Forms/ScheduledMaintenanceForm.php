<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\ScheduledMaintenances\ScheduledMaintenance;
use PCK\Exceptions\ValidationException;

class ScheduledMaintenanceForm extends FormValidator{

    protected $rules = [
        'start_time' => 'required|date',
        'end_time' => 'required|date|after:start_time',
        'message' => 'required|string|max:255',
        'image' => 'max:12288',
    ];

    protected $messages = [
        'start_time.required' => 'The start time is required.',
        'start_time.date' => 'The start time must be a valid date.',
        'end_time.required' => 'The end time is required.',
        'end_time.date' => 'The end time must be a valid date.',
        'end_time.after' => 'The end time must be after the start time.',
        'message.required' => 'The message is required.',
        'message.string' => 'The message must be a string.',
        'message.max' => 'The message may not be greater than 255 characters.',
        'image.max' => 'The image may not be greater than 12MB.',
    ];

    public function validate($formData)
    {
    	parent::validate($formData);
    }

}