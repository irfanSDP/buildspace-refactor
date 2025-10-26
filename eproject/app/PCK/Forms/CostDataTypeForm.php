<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use PCK\Buildspace\CostDataType;

class CostDataTypeForm extends CustomFormValidator {

    protected $throwException = false;

    protected $id;

    protected $rules = [
        'name' => 'required|min:3|max:200',
    ];

    public function setFormObjectId($id)
    {
        $this->id = $id;
    }

    protected function postParentValidation($formData)
    {
        $errorMessages = new MessageBag();

        if( ! $this->nameIsUnique($formData['name']) )
        {
            $errorMessages->add('name', trans('validation.unique', array( 'attribute' => strtolower(trans('general.name')) )));
        }

        return $errorMessages;
    }

    protected function nameIsUnique($name)
    {
        $record = CostDataType::where('name', '=', $name)->first();

        if( $record && ( $this->id == $record->id ) ) return true;

        return $record ? false : true;
    }
}