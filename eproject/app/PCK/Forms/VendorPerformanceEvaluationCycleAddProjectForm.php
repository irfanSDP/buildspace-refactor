<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;

class VendorPerformanceEvaluationCycleAddProjectForm extends CustomFormValidator {

    protected $throwException = false;

    protected function postParentValidation($formData)
    {
        $user = \Confide::user();

        $errors = $this->getNewMessageBag();

        $existingCycleProject = VendorPerformanceEvaluation::where('vendor_performance_evaluation_cycle_id', '=', $formData['cycle_id'])
            ->where('project_id', '=', $formData['project_id'])
            ->first();

        if($existingCycleProject)
        {
            $errors->add('form', trans('vendorManagement.error:cycleProjectExists'));
        }

        return $errors;
    }
}
