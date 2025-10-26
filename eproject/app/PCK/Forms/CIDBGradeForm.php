<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\CIDBGrades\CIDBGrade;
use PCK\Exceptions\ValidationException;

class CIDBGradeForm extends FormValidator{


	protected $rules = [
        'grade'       => 'required'
    ];

    protected $messages = [
        'grade.required' => 'The grade field is required.'
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

    private function isUnique($grade)
    {
    	$query = CIDBGrade::where('grade', 'ILIKE', $grade);

        if( $this->idToIgnore )
        {
            $query->where('id', '!=', $this->idToIgnore);
        }

        return ( $query->count() == 0 );
    }

    protected function customValidation($formData)
    {
    	$grade = $formData['grade'];

		$errorMessages = new MessageBag();

		if(! $this->isUnique($grade))
		{
			$errorMessages->add('grade', 'The grade is already in use.');
		}

		if(! $errorMessages->isEmpty())
        {
            $validationException = new ValidationException('Custom validation error');
            $validationException->setMessageBag($errorMessages);

            throw $validationException;
        }
    }
}