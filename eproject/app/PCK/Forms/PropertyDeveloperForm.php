<?php namespace PCK\Forms;

use PCK\PropertyDeveloper\PropertyDeveloper;

class PropertyDeveloperForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:250';
    }

    public function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        $recordExists = ! PropertyDeveloper::where('name', '=', $formData['name'])
            ->get()
            ->isEmpty();

        if( $recordExists )
        {
            $messageBag->add('name', trans('propertyDevelopers.recordExists'));
        }

        return $messageBag;
    }
}