<?php namespace PCK\Forms;

use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;

class ProjectDeleteForm extends CustomFormValidator {

    protected function postParentValidation($formData)
    {
        $errorMessages = $this->getNewMessageBag();

        if( $this->isInEvaluation($formData) )
        {
            $errorMessages->add('form', trans('vendorManagement.error:projectHasEvaluation'));
        }

        return $errorMessages;
    }

    protected function isInEvaluation($formData)
    {
        return VendorPerformanceEvaluation::where('project_id', '=', $formData['id'])->count() > 0;
    }
}