<?php namespace PCK\Forms;

use Carbon\Carbon;
use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\Tenders\Tender;

class TenderCallingTenderInformationForm extends FormValidator {

    CONST TAB_ID = 's3';

    protected $rules = [
        'date_of_calling_tender' => 'required|date|before:date_of_closing_tender',
        'date_of_closing_tender' => 'required|date|after:date_of_calling_tender',
        'verifiers'              => 'array',
    ];

    protected $tender;
    protected $project;

    public function validate($formData)
    {
        $this->customValidation($formData);

        return parent::validate($formData);
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();

        if( $this->tender->configuredToHaveTechnicalEvaluation() ) $rules['technical_tender_closing_date'] = 'required|date|after:date_of_calling_tender';

        return $rules;
    }

    public function setParameters(Tender $tender)
    {
        $this->tender  = $tender;
        $this->project = $tender->project;
    }

    /**
     * Runs the custom validation.
     *
     * @param $input
     *
     * @throws ValidationException
     */
    protected function customValidation($input)
    {
        $errorMessages = new MessageBag();

        if( $this->tender->configuredToHaveTechnicalEvaluation() )
        {
            if( Carbon::parse($input['technical_tender_closing_date'])->gt(Carbon::parse($input['date_of_closing_tender'])) ) $errorMessages->add('technical_tender_closing_date', trans('tenders.invalidTechnicalTenderClosingDate'));
        }

        if( isset( $input['send_to_verify'] ) && $this->tender->isFirstTender() )
        {
            if( ! $this->project->inTenderingStage() )
            {
                $errorMessages->add('tenderingStage', 'This Project has to be in the Tendering stage.');
            }

            if( ! $this->project->hasUploadedTenderDocumentFilesSkippable($this->tender) )
            {
                $errorMessages->add('tenderDocumentFiles', 'This Project does not have the required tender document files.');
            }

            if( ! $this->project->formOfTenderEditedSkippable($this->tender) )
            {
                $errorMessages->add('formOfTender', 'The form of tender has not been updated.');
            }
        }

        if( isset( $input['send_to_verify'] ) && ( ! $this->tender->isFirstTender() ) )
        {
            if( ! $this->project->addendumFinalised($this->tender) )
            {
                $errorMessages->add('addendum', 'The addendum needs to be finalised.');
            }
        }

        if( ! $errorMessages->isEmpty() )
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }

}