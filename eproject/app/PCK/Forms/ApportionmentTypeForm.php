<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\AccountCodeSettings\ApportionmentType;

class ApportionmentTypeForm extends FormValidator
{
    private $apportionmentTypeId;

    protected $rules = [
        'name'       => 'required',
    ];

    protected $messages = [
        'name.required' => 'Apportionment type name is required',
    ];

    public function validate($formData)
    {
        $isNewApportionmentType = is_null($this->apportionmentTypeId);

        parent::validate($formData);

        // new category
        if($isNewApportionmentType)
        {
            $this->customUniqueNameValidation($formData);
        }
        else
        {
            $apportionmentType = ApportionmentType::find($this->apportionmentTypeId);

            // existing apportionmentType, name changes, check for uniqueness
            if($apportionmentType->name != $formData['name'])
            {
                $this->customUniqueNameValidation($formData);
            }
        }
    }

    public function setParameters($apportionmentTypeId)
    {
        $this->apportionmentTypeId = $apportionmentTypeId;
    }

    protected function customUniqueNameValidation($formData)
    {
        $errorMessages = $this->uniqueNameValidation($formData);

        if( !$errorMessages->isEmpty() )
        {
            $validationException = new ValidationException('Apportionment Unique Name Error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }

    protected function uniqueNameValidation($formData)
    {
        $errorMessages = new MessageBag();
        $name = $formData['name'];
        $isNameUnique = ApportionmentType::nameIsUnique($name);

        if (!$isNameUnique)
        {
            $errorMessages->add('name', trans('accountCodes.apportionmentTypeNameNotUnique'));
        } 

        return $errorMessages;
    }
}

