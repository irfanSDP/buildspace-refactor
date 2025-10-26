<?php namespace PCK\Forms;

use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationSetup;

class VendorPerformanceEvaluationSetupCompanyFormChangeForm extends CustomFormValidator {

    protected function postParentValidation($formData)
    {
        $errorMessages = $this->getNewMessageBag();

        if( $this->hasCompletedForms($formData) )
        {
            $errorMessages->add('form', trans('vendorManagement.error:formsCompleted'));
        }

        return $errorMessages;
    }

    protected function hasCompletedForms($formData)
    {
        $setup = VendorPerformanceEvaluationSetup::find($formData['id']);

        foreach($setup->getCompanyForms() as $form)
        {
            if($form->isCompleted()) return true;
        }

        return false;
    }
}