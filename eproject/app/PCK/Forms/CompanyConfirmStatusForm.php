<?php namespace PCK\Forms;

use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;

class CompanyConfirmStatusForm extends CustomFormValidator
{
    protected $throwException = false;

    protected function setRules($formData)
    {
        $rejectOptions = [ContractorCommitmentStatus::REJECT , ContractorCommitmentStatus::TENDER_WITHDRAW];

        if( in_array($formData['option'], $rejectOptions) )
        {
            $this->rules['remarks'] = 'required';
        }
    }

    protected $messages = [
        'remarks.required' => 'Remarks are required.',
    ];
}