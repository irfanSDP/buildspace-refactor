<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ProcurementMethodForm extends FormValidator {

    protected $rules = [
        'name' => 'required|min:3|max:50|unique:procurement_methods',
    ];

    public function ignoreUnique($id)
    {
        $this->rules['name'] = $this->rules['name'] . ',name,' . $id;
    }

}