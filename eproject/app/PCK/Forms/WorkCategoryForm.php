<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Exceptions\ValidationException;
use PCK\WorkCategories\WorkCategory;

class WorkCategoryForm extends FormValidator {

    /**
     * Validation rules for creating a new Resource
     *
     * @var array
     */
    protected $rules = [
        'name'       => 'required|min:3|max:50',
        'identifier' => 'required|min:2|max:10|regex:/[A-Za-z]*/',
    ];

    protected $messages = [
        'identifier.regex' => 'The identifier must only contain alphabetical characters.',
    ];

    private $idToIgnore = null;

    /**
     * User can set the validator to ignore a specific record.
     * Used to ignore the current record when checking uniqueness.
     *
     * @param $id
     */
    public function ignoreUnique($id)
    {
        $this->idToIgnore = $id;
    }

    public function validate($formData)
    {
        $this->customValidation($formData);

        parent::validate($formData);
    }

    private function nameIsUnique($name)
    {
        $query = WorkCategory::where('name', 'ilike', $name);

        if( $this->idToIgnore )
        {
            $query->where('id', '!=', $this->idToIgnore);
        }

        return ( $query->count() == 0 );
    }

    private function identifierIsUnique($identifier)
    {
        $query = WorkCategory::where('identifier', '=', $identifier);

        if( $this->idToIgnore )
        {
            $query->where('id', '!=', $this->idToIgnore);
        }

        return ( $query->count() == 0 );
    }

    /**
     * Validates uniqueness in name and identifier.
     * Laravel's validation for uniqueness is not used because ilike is not supported.
     *
     * @param $formData
     *
     * @throws ValidationException
     */
    protected function customValidation($formData)
    {
        $errorMessages = new MessageBag();

        if( ! $this->nameIsUnique($formData['name']) )
        {
            $errorMessages->add('name', 'The name is already in use.');
        }

        if( ! $this->identifierIsUnique($formData['identifier']) )
        {
            $errorMessages->add('identifier', 'The identifier is already in use.');
        }

        if( ! $errorMessages->isEmpty() )
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }

}