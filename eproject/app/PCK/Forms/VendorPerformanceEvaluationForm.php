<?php namespace PCK\Forms;

use Carbon\Carbon;

class VendorPerformanceEvaluationForm extends CustomFormValidator {
    protected $evaluation;

    protected function setRules($formData)
    {
        $this->rules['start_date'] = 'required|date|before:end_date';
        $this->rules['end_date'] = 'required|date|after:start_date';
    }

    protected function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        $startDate = Carbon::parse($this->evaluation->cycle->start_date);
        $endDate   = Carbon::parse($this->evaluation->cycle->end_date);

        if(Carbon::parse($formData['start_date'])->lt($startDate))
        {
            $messageBag->add('start_date', trans('vendorManagement.startDateCannotBeEarlierThan', array('startDate' => $startDate->format(\Config::get('dates.submitted_at')))));
        }

        if(Carbon::parse($formData['end_date'])->gt($endDate))
        {
            $messageBag->add('end_date', trans('vendorManagement.startDateCannotBeLaterThan', array('endDate' => $endDate->format(\Config::get('dates.submitted_at')))));
        }

        return $messageBag;
    }

    public function setEvaluation($evaluation)
    {
        $this->evaluation = $evaluation;
    }
}