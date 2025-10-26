<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\CIDBCodes\CIDBCode;
use PCK\Exceptions\ValidationException;

class CIDBCodeForm extends FormValidator{


	protected $rules = [
        'code'          => 'required',
        'description'   => 'required',
    ];

    protected $messages = [
        'code.required' => 'The code field is required.',
        'description.required' => 'The description field is required.',
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

    private function codeIsUnique($code)
    {
    	$query = CIDBCode::where('code', 'ILIKE', $code);


        if( $this->idToIgnore )
        {
            $query->where('id', '!=', $this->idToIgnore);
        }

        return ( $query->count() == 0 );
    }

    private function descriptionIsUnique($description)
    {
    	$query = CIDBCode::where('description', 'ILIKE', $description);


        if( $this->idToIgnore )
        {
            $query->where('id', '!=', $this->idToIgnore);
        }

        return ( $query->count() == 0 );
    }

    protected function customValidation($formData)
    {
    	$code = $formData['code'];
        $description = $formData['description'];

		$errorMessages = new MessageBag();

		if(! $this->codeIsUnique($code))
		{
			$errorMessages->add('code', 'The code is already in use.');
		}

        if(! $this->descriptionIsUnique($description))
		{
			$errorMessages->add('description', 'The description is already in use.');
		}

		if(! $errorMessages->isEmpty())
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }


    public function validateChildren($formData)
    {
    	$this->customValidationChildren($formData);

    	// parent::validateChildren($formData);
    }

    public function ignoreIdChildren($id){

        $this->idToIgnore = $id; 
    }

    private function codeChildrenIsUnique($code)
    {
    	$query = CIDBCode::where("parent_id", NULL)->where('code', 'ILIKE', $code);


        if( $this->idToIgnore )
        {
            $query->where('id', '!=', $this->idToIgnore);
        }

        return ( $query->count() == 0 );
    }

    private function descriptionChildrenIsUnique($description)
    {
    	$query = CIDBCode::where("parent_id", NULL)->where('description', 'ILIKE', $description);


        if( $this->idToIgnore )
        {
            $query->where('id', '!=', $this->idToIgnore);
        }

        return ( $query->count() == 0 );
    }

    protected function customValidationChildren($formData)
    {
    	$code = $formData['code'];
        $description = $formData['description'];

		$errorMessages = new MessageBag();

		if(! $this->codeChildrenIsUnique($code))
		{
			$errorMessages->add('code', 'The code is already in use.');
		}

        if(! $this->descriptionChildrenIsUnique($description))
		{
			$errorMessages->add('description', 'The description is already in use.');
		}

		if(! $errorMessages->isEmpty())
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }
}