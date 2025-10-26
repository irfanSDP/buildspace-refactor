<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidationException;

class TechnicalEvaluationSetReferenceForm extends \PCK\Forms\CustomFormValidator {

    private $workCategory;
    private $contractLimit;

    public function setParameters($workCategory, $contractLimit)
    {
        $this->workCategory  = $workCategory;
        $this->contractLimit = $contractLimit;
    }

    protected function setRules($formData)
    {
        if( ! empty( $formData['templateId'] ) )
        {
            $this->rules['templateId'] = "exists:technical_evaluation_set_references,id,id,{$formData['templateId']}";

            $this->messages['templateId.exists'] = trans('technicalEvaluation.copyFromTemplateError');
        }

    }

    protected function preParentValidation($formData)
    {
        $errorMessages = new MessageBag();

        $repository = \App::make('\PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository');

        if( $repository->findTemplate($this->workCategory, $this->contractLimit) )
        {
            $errorMessages->add('general', 'The technical evaluation is already defined for this category.');
        }

        return $errorMessages;
    }

}