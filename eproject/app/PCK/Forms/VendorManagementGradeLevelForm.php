<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\ModuleParameters\VendorManagement\VendorManagementGradeLevel;

class VendorManagementGradeLevelForm extends CustomFormValidator
{
    protected $rules = [
        'description'       => 'required|max:200',
        'score_upper_limit' => 'required|integer|min:1',
    ];

    protected $messages = [
        'description.required' => 'Level description is required.',

        'score_upper_limit.required' => 'Value is required.',
        'score_upper_limit.integer'  => 'Value must be a valid whole number.',
        'score_upper_limit.min'      => 'Value must not be less than :min.',
    ];

    public function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        $isNewRecord       = isset($formData['levelId']) ? false : true;
        $hasDuplicateScore = false;

        if($isNewRecord)
        {
            $hasDuplicateScore = (VendorManagementGradeLevel::where('vendor_management_grade_id', $formData['gradeId'])->where('score_upper_limit', $formData['score_upper_limit'])->count() > 0);
        }
        else
        {
            $level             = VendorManagementGradeLevel::find($formData['levelId']);
            $hasDuplicateScore = (VendorManagementGradeLevel::where('vendor_management_grade_id', $level->grade->id)->where('score_upper_limit', $formData['score_upper_limit'])->where('id', '!=', $level->id)->count() > 0);
        }

        if($hasDuplicateScore)
        {
            $messageBag->add('score_upper_limit', trans('vendorManagement.duplicatedScoreFound'));
        }

        return $messageBag;
    }
}

