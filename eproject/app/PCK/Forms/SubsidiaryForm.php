<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;
use PCK\Forms\CustomFormValidator;
use PCK\Subsidiaries\Subsidiary;

class SubsidiaryForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['name'] = 'required|min:1|max:250|iunique:subsidiaries,name,'.$formData['id'].',id';
        $this->rules['identifier'] = 'required|min:1|max:50|iunique:subsidiaries,identifier,'.$formData['id'].',id';

        if(!empty($formData['parent_id']) && (int)$formData['parent_id'] > 0)
        {
            $this->rules['parent_id'] = 'exists:subsidiaries,id';
        }

        $this->messages['name.iunique'] = "Name has already been taken";
        $this->messages['identifier.iunique'] = "Identifier has already been taken";
    }
}