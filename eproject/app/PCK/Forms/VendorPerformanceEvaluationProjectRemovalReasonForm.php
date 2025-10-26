<?php namespace PCK\Forms;

use PCK\VendorPerformanceEvaluation\ProjectRemovalReason;

class VendorPerformanceEvaluationProjectRemovalReasonForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:250';
    }

    public function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        $recordExists = ! ProjectRemovalReason::where('name', '=', $formData['name'])
            ->get()
            ->isEmpty();

        if( $recordExists )
        {
            $messageBag->add('name', trans('businessEntityTypes.recordExists'));
        }

        return $messageBag;
    }
}