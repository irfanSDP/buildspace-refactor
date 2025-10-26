<?php namespace PCK\DigitalStar\Forms;

use Illuminate\Support\MessageBag;
use PCK\Forms\CustomFormValidator;
use PCK\DigitalStar\Evaluation\DsCycle;

class DsCycleForm extends CustomFormValidator {
    protected $mode = 'store';

    public function setUpdateMode()
    {
        $this->mode = 'update';
    }

    protected function preParentValidation($formData)
    {
        $errors = new MessageBag();

        if(($this->mode == 'store') && DsCycle::hasOngoingCycle())
        {
            $errors->add('form', trans('digitalStar/vendorManagement.error:inProgressCycleExists'));
        }

        return $errors;
    }

    protected function setRules($formData)
    {
        $this->rules['start_date']  = 'required|date|before:end_date';
        $this->rules['end_date']    = 'required|date|after:start_date';
        //$this->rules['project_ids'] = 'array';
    }
}