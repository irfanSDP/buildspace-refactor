<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\RequestForVariation\RequestForVariationCategory;

class RfvCategoryForm extends FormValidator
{
    private $rfvCategoryId;

    protected $rules = [
        'description'       => 'required',
    ];

    protected $messages = [
        'description.required' => 'RFV category description field is required.',
    ];

    public function validate($formData)
    {
        $isNewRfvCategory = is_null($this->rfvCategoryId);

        parent::validate($formData);

        // new category
        if($isNewRfvCategory)
        {
            $this->customUniqueDescriptionValidation($formData);
        }
        else
        {
            $rfvCategory = RequestForVariationCategory::find($this->rfvCategoryId);

            // existing category, description changes, check for uniqueness
            if($rfvCategory->name != $formData['description'])
            {
                $this->customUniqueDescriptionValidation($formData);
            }
        }
    }

    public function setParameters($rfvCategoryId)
    {
        $this->rfvCategoryId = $rfvCategoryId;
    }

    protected function customUniqueDescriptionValidation($formData)
    {
        $errorMessages = $this->uniqueDescriptionValidation($formData);

        if( !$errorMessages->isEmpty() )
        {
            $validationException = new ValidationException('Custom validation selected contractors error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }

    protected function uniqueDescriptionValidation($formData)
    {
        $errorMessages = new MessageBag();
        $description = $formData['description'];
        $isDescriptionUnique = RequestForVariationCategory::descriptionIsUnique($description);

        if (!$isDescriptionUnique)
        {
            $errorMessages->add('description', trans('requestForVariation.description_not_unique'));
        } 

        return $errorMessages;
    }
}

