<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Defects\Defect;
use PCK\Exceptions\ValidationException;

class DefectForm extends FormValidator{


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

    private function isUnique($defect_name, $defectcategory_id)
    {
    	$query = Defect::where('defect_category_id', $defectcategory_id)
							 ->where('name', 'ILIKE', $defect_name);

		if( $this->idToIgnore )
        {
            $query->where('id', '!=', $this->idToIgnore);
        }

        return ( $query->count() == 0 );
    }

    protected function customValidation($formData)
    {
    	$defectcategory_id = $formData['defect_category_id'];
    	$defect_name = $formData['name'];

		$errorMessages = new MessageBag();

		if(! $this->isUnique($defect_name, $defectcategory_id))
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