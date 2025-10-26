<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\EBiddings\EBidding;
use PCK\Exceptions\ValidationException;

class EBiddingsForm extends FormValidator{

    protected $rules = [
        'preview_start_time'    => 'required|date',
        'bidding_start_time'    => 'required|date|after:preview_start_time',
        'duration_hours'        => 'integer|min:0',
        'duration_minutes'      => 'integer|min:0',
        'duration_seconds'      => 'integer|min:0',
        'start_overtime'        => 'integer|min:0',
        'start_overtime_seconds'=> 'integer|min:0',
        'overtime_period'       => 'integer|min:0',
        'overtime_seconds'      => 'integer|min:0',
        'budget'                => 'numeric|min:0',
        'decrement_percent'     => 'numeric|min:1|max:99',
        'decrement_value'       => 'numeric|min:1',
        'min_bid_amount_diff'   => 'numeric|min:1',
    ];

    protected $messages = [
        'preview_start_time.required'    => 'This is required',
        'preview_start_time.date'        => 'This must be a valid date',
        'bidding_start_time.required'    => 'This is required',
        'bidding_start_time.date'        => 'This must be a valid date',
        'bidding_start_time.after'       => 'The bidding start time must be after the preview start time',
        'duration_hours.integer'         => 'This must be an integer',
        'duration_hours.min'             => 'This must not be less than :min',
        'duration_minutes.integer'       => 'This must be an integer',
        'duration_minutes.min'           => 'This must not be less than :min',
        'duration_seconds.integer'       => 'This must be an integer',
        'duration_seconds.min'           => 'This must not be less than :min',
        'start_overtime.integer'         => 'This must be an integer',
        'start_overtime.min'             => 'This must not be less than :min',
        'start_overtime_seconds.integer' => 'This must be an integer',
        'start_overtime_seconds.min'     => 'This must not be less than :min',
        'overtime_period.integer'        => 'This must be an integer',
        'overtime_period.min'            => 'This must not be less than :min',
        'overtime_seconds.integer'       => 'This must be an integer',
        'overtime_seconds.min'           => 'This must not be less than :min',
        'budget.numeric'                 => 'This must be a numeric value',
        'budget.min'                     => 'This must not be less than :min',
        'decrement_percent.required_if'  => 'The bid decrement percentage is required when the "Not Applicable" checkbox is not checked',
        'decrement_percent.numeric'      => 'This must be a numeric value',
        'decrement_percent.min'          => 'This must not be less than :min',
        'decrement_percent.max'          => 'This must not exceed :max',
        'decrement_value.required_if'    => 'The bid decrement value is required when the "Not Applicable" checkbox is not checked',
        'decrement_value.numeric'        => 'This must be a numeric value',
        'decrement_value.min'            => 'This must not be less than :min',
        'min_bid_amount_diff.numeric'    => 'This must be a numeric value',
        'min_bid_amount_diff.min'        => 'This must not be less than :min',
    ];

    public function validate($formData)
    {
    	parent::validate($formData);
    }

}