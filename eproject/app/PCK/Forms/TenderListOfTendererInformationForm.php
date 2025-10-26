<?php namespace PCK\Forms;

use Carbon\Carbon;
use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\Projects\Project;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformation;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;

class TenderListOfTendererInformationForm extends FormValidator {

    CONST TAB_ID = 's2';

    private $project;
    private $technicalEvaluationSetReferenceRepository;
    private $contractLimitRepository;

    protected $rules = [
        'date_of_calling_tender'       => 'required|date|before:date_of_closing_tender',
        'date_of_closing_tender'       => 'required|date|after:date_of_calling_tender',
        'verifiers'                    => 'array',
        'completion_period'            => 'required|numeric|min:1.0|max:999.9|regex:/^\d*(\.\d{1,1})?$/',
        'project_incentive_percentage' => 'numeric|max:999999.99',
        'procurement_method_id'        => 'integer',
    ];

    protected function addTechnicalTenderClosingDateValidation()
    {
        $this->rules['technical_tender_closing_date'] = 'required|date|after:date_of_calling_tender';
    }

    protected function addSelectedContractorsValidation() {
        $this->rules['status'] = 'required|array|min:1';
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

        if( $this->technicalEvaluationSetReferenceRepository->getSetReferenceByProject($this->project) ) return $errorMessages;

        if( Carbon::parse($formData['technical_tender_closing_date'])->gt(Carbon::parse($formData['date_of_closing_tender'])) ) $errorMessages->add('technical_tender_closing_date', trans('tenders.invalidTechnicalTenderClosingDate'));

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

        $lotInfoId = $formData['listOfTendererInformationId'];
        $lotInfo = TenderListOfTendererInformation::find($lotInfoId);
        $selectedContractors = $lotInfo->selectedContractors;

        // if no contractors are selected
        if($selectedContractors->isEmpty()) {
            $errorMessages->add('min_one_contractor_required_LOT', trans('tenders.min_one_contractor_required'));
            return $errorMessages;
        }

        $nonDeletedContractors = array();
        
        foreach($selectedContractors as $contractor) {
            if($contractor->pivot->deleted_at === NULL) {
                array_push($nonDeletedContractors, $contractor);
            }
        }

        // if each and every contractor is deleted
        if(count($nonDeletedContractors) === 0) {
            $errorMessages->add('min_one_status_yes_required_LOT', trans('tenders.min_one_status_yes_not_deleted_required'));
            return $errorMessages;
        }

        // pending status is not allowed
        foreach($nonDeletedContractors as $contractor)
        {
            $contractorStatus = $formData['status'][$contractor->id];

            if($contractorStatus == ContractorCommitmentStatus::PENDING)
            {
                $errorMessages->add('LOT_selected_contractor_status_pending', trans('tenders.all_contractors_status_must_not_be_pending'));

                return $errorMessages;
            }
        }

        $validContractorsCount = 0;

        foreach($nonDeletedContractors as $contractor) {
            $contractorStatus = $formData['status'][$contractor->id];
            if($contractorStatus == ContractorCommitmentStatus::OK) {
                ++$validContractorsCount;
            }
        }

        // need at least 1 contractor's status is set to yes and not deleted
        if($validContractorsCount === 0) {
            $errorMessages->add('min_one_status_yes_required_LOT', trans('tenders.min_one_status_yes_not_deleted_required'));
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