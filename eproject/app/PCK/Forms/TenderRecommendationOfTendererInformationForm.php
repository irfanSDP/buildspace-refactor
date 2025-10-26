<?php namespace PCK\Forms;

use Carbon\Carbon;
use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\Projects\Project;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;

class TenderRecommendationOfTendererInformationForm extends FormValidator {

    CONST TAB_ID = 's1';

    private $project;
    private $technicalEvaluationSetReferenceRepository;
    private $contractLimitRepository;

    protected $rules = [
        'proposed_date_of_calling_tender' => 'required|date|before:proposed_date_of_closing_tender',
        'proposed_date_of_closing_tender' => 'required|date|after:proposed_date_of_calling_tender',
        'target_date_of_site_possession'  => 'required|date',
        'budget'                          => 'required|numeric',
        'consultant_estimates'            => 'numeric',
        'verifiers'                       => 'array',
        'completion_period'               => 'required|numeric|min:1.0|max:999.9|regex:/^\d*(\.\d{1,1})?$/',
        'completion_period_metric'        => 'required|integer|min:0',
        'project_incentive_percentage'    => 'numeric',
        'procurement_method_id'           => 'integer'
    ];

    protected function addTechnicalTenderClosingDateValidation()
    {
        $this->rules['technical_tender_closing_date'] = 'required|date|after:proposed_date_of_calling_tender';
    }

    protected function addSelectedContractorsValidation() 
    {
        $this->rules['status'] = 'array|min:1';
    }

    public function validate($formData)
    {
        if( $formData['technical_evaluation_required'] ?? false ) $this->addTechnicalTenderClosingDateValidation();

        if( isset($formData['send_to_verify']) ) {
            $this->addSelectedContractorsValidation();
            $this->customValidationSelectedContractors($formData);
        }

        parent::validate($formData);

        $this->technicalEvaluationSetReferenceRepository = \App::make('PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository');
        $this->contractLimitRepository                   = \App::make('PCK\ContractLimits\ContractLimitRepository');

        $this->customValidation($formData);
    }

    public function setParameters(Project $project)
    {
        $this->project = $project;
    }

    protected function technicalEvaluationValidation($formData)
    {
        $errorMessages = new MessageBag();

        if( ! ( $formData['technical_evaluation_required'] ?? false ) ) return $errorMessages;

        if( Carbon::parse($formData['technical_tender_closing_date'])->gt(Carbon::parse($formData['proposed_date_of_closing_tender'])) ) $errorMessages->add('technical_tender_closing_date', trans('tenders.invalidTechnicalTenderClosingDate'));

        $contractLimit = null;

        if( ! empty( $formData['contract_limit_id'] ) ) $contractLimit = $this->contractLimitRepository->find($formData['contract_limit_id']);

        if( ! $this->technicalEvaluationSetReferenceRepository->findTemplate($this->project->workCategory, $contractLimit) )
        {
            $errorMessages->add('technical_evaluation_required', 'No technical evaluation is defined for this category.');
        }

        return $errorMessages;
    }

    protected function selectedContractorsValidation($formData) {
        $errorMessages = new MessageBag();

        $selectedContractors = ($formData['status'] ?? null);

        if ($selectedContractors ) {
            $arrValues = array_values($selectedContractors);
            if( !in_array(ContractorCommitmentStatus::OK, $arrValues) ) {
                $errorMessages->add('min_one_status_yes_required', trans('tenders.min_one_status_yes_required'));
            }
        }

        return $errorMessages;
    }

    protected function customValidation($formData)
    {
        $errorMessages = $this->technicalEvaluationValidation($formData);

        if( ! $errorMessages->isEmpty() )
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }

    protected function customValidationSelectedContractors($formData) {
        $errorMessages = $this->selectedContractorsValidation($formData);
        
        if( !$errorMessages->isEmpty() ) {
            $validationException = new ValidationException('Custom validation selected contractors error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }

}