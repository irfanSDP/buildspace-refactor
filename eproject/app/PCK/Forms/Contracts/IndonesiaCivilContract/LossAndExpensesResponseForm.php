<?php namespace PCK\Forms\Contracts\IndonesiaCivilContract;

use Laracasts\Validation\FormValidator;
use PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse;

class LossAndExpensesResponseForm extends FormValidator {

    protected $rules = [
        'subject' => 'required|max:200',
        'content' => 'required',
        'type'    => 'required|integer',
    ];

    public function validate($formData)
    {
        if( ( $formData['type'] ) == ContractualClaimResponse::TYPE_GRANT )
        {
            $this->rules['proposed_value'] = array(
                'required',
                'numeric',
                'min:0',
                'max:9999999999999999.99',
            );
        }

        return parent::validate($formData);
    }

}