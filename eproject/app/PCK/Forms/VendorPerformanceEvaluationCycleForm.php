<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use PCK\VendorPerformanceEvaluation\Cycle;

class VendorPerformanceEvaluationCycleForm extends CustomFormValidator {
    protected $mode = 'store';

    public function setUpdateMode()
    {
        $this->mode = 'update';
    }

    protected function preParentValidation($formData)
    {
        $errors = new MessageBag();

        if(($this->mode == 'store') && Cycle::hasOngoingCycle())
        {
            $errors->add('form', trans('vendorManagement.error:inProgressCycleExists'));
        }

        return $errors;
    }

    protected function setRules($formData)
    {
        $this->rules['start_date']  = 'required|date|before:end_date';
        $this->rules['end_date']    = 'required|date|after:start_date';
        $this->rules['project_ids'] = 'array';
    }
}