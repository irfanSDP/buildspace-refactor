<?php namespace PCK\DigitalStar\Forms;

use PCK\Forms\CustomFormValidator;
use PCK\DigitalStar\Evaluation\DsEvaluation;

class DsCycleAddCompanyForm extends CustomFormValidator {

    protected $throwException = false;

    protected function postParentValidation($formData)
    {
        $user = \Confide::user();

        $errors = $this->getNewMessageBag();

        $existingCycleProject = DsEvaluation::where('ds_cycle_id', '=', $formData['cycle_id'])
            ->where('company_id', '=', $formData['company_id'])
            ->first();

        if($existingCycleProject)
        {
            $errors->add('form', trans('digitalStar/digitalStar.errorCycleCompanyExists'));
        }

        return $errors;
    }
}
