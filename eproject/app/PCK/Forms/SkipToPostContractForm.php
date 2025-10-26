<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Companies\Company;
use PCK\ContractGroups\Types\Role;
use PCK\Exceptions\ValidationException;

class SkipToPostContractForm extends FormValidator {

    protected $rules = [
        'contractor_id' => 'required|integer|min:1',
        'trade'         => 'required'
    ];

    public function validate($formData)
    {
        parent::validate($formData);

        $this->customValidation($formData);
    }

    protected function customValidation($formData)
    {
        $errorMessages = new MessageBag();

        if( ! isset( $formData['contractor_id'] ) )
        {
            $errorMessages->add('contractor_id', 'No company is selected.');
        }
        else
        {
            $company = Company::find($formData['contractor_id']);

            if( ! $company )
            {
                $errorMessages->add('contractor_id', 'The company does not exist.');
            }

            if( ! $company->contractGroupCategory->includesContractGroups(Role::CONTRACTOR) )
            {
                $errorMessages->add('contractor_id', 'The company is not classified as a contractor and therefore cannot be selected.');
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