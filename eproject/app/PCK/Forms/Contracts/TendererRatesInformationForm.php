<?php namespace PCK\Forms\Contracts;

use Laracasts\Validation\FormValidator;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformation;

class TendererRatesInformationForm extends FormValidator {

    public function reAdjustValidationRule(TenderCallingTenderInformation $callingTenderInformation)
    {
        $project = $callingTenderInformation->tender->project;
        $bsProjectMainInformation = $project->getBsProjectMainInformation();

        if($bsProjectMainInformation && $bsProjectMainInformation->projectStructure->tenderAlternatives->count())
        {
            foreach($bsProjectMainInformation->projectStructure->tenderAlternatives as $tenderAlternative)
            {
                $this->rules['discounted_percentage.'.$tenderAlternative->id] = 'sometimes|numeric';
                $this->rules['discounted_amount.'.$tenderAlternative->id]     = 'sometimes|numeric';
            }
        }
        else
        {
            $this->rules['discounted_percentage'] = 'sometimes|numeric';
            $this->rules['discounted_amount']     = 'sometimes|numeric';
        }

        if( $callingTenderInformation->allowContractorProposeOwnCompletionPeriod() )
        {
            if($bsProjectMainInformation && $bsProjectMainInformation->projectStructure->tenderAlternatives->count())
            {
                foreach($bsProjectMainInformation->projectStructure->tenderAlternatives as $tenderAlternative)
                {
                    $this->rules['contractor_adjustment_percentage.'.$tenderAlternative->id] = 'sometimes|numeric';
                    $this->rules['contractor_adjustment_amount.'.$tenderAlternative->id]     = 'sometimes|numeric';
                    $this->rules['completion_period.'.$tenderAlternative->id]                = 'sometimes|numeric|min:1.0|max:999.9|regex:/^\d*(\.\d{1,1})?$/';
                }
            }
            else
            {
                $this->rules['contractor_adjustment_percentage'] = 'sometimes|numeric';
                $this->rules['contractor_adjustment_amount']     = 'sometimes|numeric';
                $this->rules['completion_period']                = 'sometimes|numeric|min:1.0|max:999.9|regex:/^\d*(\.\d{1,1})?$/';
            }
            
        }

        $this->messages['numeric'] = "This field must be a number";
        $this->messages['min'] = "This field must be at least :min";
        $this->messages['max'] = "This field may not be greater than :max";

    }

}