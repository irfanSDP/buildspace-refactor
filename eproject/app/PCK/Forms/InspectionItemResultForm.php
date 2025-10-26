<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;

class InspectionItemResultForm extends CustomFormValidator {

    protected $allowedFields = array(
    	'progress_status',
    	'remarks',
    );

    protected function setRules($formData)
    {
    	if( array_key_exists('progress_status', $formData) )
    	{
    		$this->rules['progress_status'] = 'numeric|between:0,100';
    	}
    }

    protected function preParentValidation($formData)
    {
    	$errors = new MessageBag();

    	foreach($formData as $field => $value)
    	{
    		if( ! in_array($field, $this->allowedFields) )
    		{
    			$errors->add($field, 'Error updating this field');
    		}
    	}

        return $errors;
    }
}