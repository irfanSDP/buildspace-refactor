<?php namespace PCK\Forms\Contracts\IndonesiaCivilContract;

use Laracasts\Validation\FormValidator;
use PCK\IndonesiaCivilContract\ContractualClaimResponse\ContractualClaimResponse;

class ExtensionOfTimeResponseForm extends FormValidator {

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
                'integer',
                'min:0',
                'max:9999999999999999',
            );
        }

        return parent::validate($formData);
    }

}