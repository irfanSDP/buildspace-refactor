<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Weathers\Weather; 
use PCK\Exceptions\ValidationException;

class WeatherForm extends FormValidator{


	protected $rules = [
        'name'       => 'required'
    ];

    protected $messages = [
        'name.required' => 'The name field is required.'
    ];

    private $idToIgnore = null;

    public function validate($formData)
    {
    	$this->customValidation($formData);

    	parent::validate($formData);
    }

    public function ignoreId($id){

        $this->idToIgnore = $id; 
    }

    private function isUnique($name)
    {
    	$query = Weather::where('name', 'ILIKE', $name);

        if( $this->idToIgnore )
        {
            $query->where('id', '!=', $this->idToIgnore);
        }

        return ( $query->count() == 0 );
    }

    protected function customValidation($formData)
    {
    	$name = $formData['name'];

		$errorMessages = new MessageBag();

		if(! $this->isUnique($name))
		{
			$errorMessages->add('name', 'The name is already in use.');
		}

		if(! $errorMessages->isEmpty())
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }
}