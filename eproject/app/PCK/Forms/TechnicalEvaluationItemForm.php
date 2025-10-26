<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\TechnicalEvaluationItems\TechnicalEvaluationItem;

class TechnicalEvaluationItemForm extends FormValidator {

    /**
     * Validation rules for creating a new Resource.
     *
     * @var array
     */
    protected $rules = [
        'name'  => 'required|min:2|max:300',
        'value' => 'required|numeric|min:0',
    ];

    private $repository;

    /**
     * Validates the form data.
     *
     * @param mixed $formData
     *
     * @return bool
     * @throws ValidationException
     * @throws \Laracasts\Validation\FormValidationException
     */
    public function validate($formData)
    {
        $repository       = \App::make('PCK\TechnicalEvaluationItems\TechnicalEvaluationItemRepository');
        $this->repository = $repository;

        parent::validate($formData);

        $this->customValidation($formData);

        return true;
    }

    /**
     * Runs the custom validation.
     *
     * @param $formData
     *
     * @throws ValidationException
     */
    protected function customValidation($formData)
    {
        $errorMessages = new MessageBag();

        if( empty( $formData['id'] ) )
        {
            $item = $this->repository->createNew($formData);
        }
        else
        {
            $item = TechnicalEvaluationItem::find($formData['id']);
        }

        if( $formData['value'] > $item->getMaxValidValue() )
        {
            $errorMessages->add('value', 'The ' . $item->valueName() . ' can have a maximum value of ' . $item->getMaxValidValue());
        }

        if( ! $errorMessages->isEmpty() )
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }

}