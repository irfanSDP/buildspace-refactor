<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\ModuleParameters\VendorManagement\VendorPerformanceEvaluationModuleParameter;

class VendorPerformanceEvaluationModuleParameterForm extends CustomFormValidator
{
    protected $messages = [
        'default_time_frame_for_vpe_cycle_value.required'             => 'Value is required',
        'default_time_frame_for_vpe_cycle_value.integer'              => 'Value must be a valid integer',
        'default_time_frame_for_vpe_cycle_value.numeric'              => 'Value must be a number',
        'default_time_frame_for_vpe_cycle_value.min'                  => 'Value must not be less than :min',
        'default_time_frame_for_vpe_cycle_value.max'                  => 'Value must not be more than :max',

        'default_time_frame_for_vpe_submission_value.required' => 'Value is required.',
        'default_time_frame_for_vpe_submission_value.integer'  => 'Value must be a valid integer.',
        'default_time_frame_for_vpe_submission_value.numeric'  => 'Value must be a valid number.',
        'default_time_frame_for_vpe_submission_value.min'      => 'Value must not be less than :min',
        'default_time_frame_for_vpe_submission_value.max'      => 'Value must not be more than :max',

        'passing_score.required' => 'Value is required.',
        'passing_score.integer'  => 'Value must be a valid integer.',
        'passing_score.min'      => 'Value must not be less than :min',
        'passing_score.max'      => 'Value must not be more than :max',

        'attachments_required_score_threshold.numeric'  => 'Value must be a valid number.',
        'attachments_required_score_threshold.min'      => 'Value must not be less than :min',
        'attachments_required_score_threshold.max'      => 'Value must not be more than :max',

        'email_reminder_before_cycle_end_date_value.required' => 'Value is required',
        'email_reminder_before_cycle_end_date_value.integer'  => 'Value must be a valid integer',
        'email_reminder_before_cycle_end_date_value.min'      => 'Value must be :min or more',
    ];

    protected $rules = [
        'number_of_days_ahead_of_submission'         => 'array',
        'attachments_required_score_threshold'       => 'numeric|min:0|max:100',
        'passing_score'                              => 'integer|min:0|max:100',
        'email_reminder_before_cycle_end_date_value' => 'integer|min:1',
    ];

    protected function setRules($formData)
    {
        $this->rules['default_time_frame_for_vpe_cycle_value']      = 'required|integer|min:1|max:1000';
        $this->rules['default_time_frame_for_vpe_submission_value'] = 'required|integer|min:1|max:1000';
    }

    protected function preParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        if(!empty($formData['number_of_days_ahead_of_submission']))
        {
            foreach($formData['number_of_days_ahead_of_submission'] as $key => $input)
            {
                if(filter_var($input, FILTER_VALIDATE_INT)) continue;

                $messageBag->add('number_of_days_ahead_of_submission', trans('forms.error:integers'));

                break;
            }
        }

        return $messageBag;
    }
}