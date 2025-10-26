<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ContractLimitForm extends FormValidator {

    protected $rules = [
        'limit' => 'required|min:3|max:150|unique:contract_limits',
    ];

    public function ignoreUnique($id)
    {
        $this->rules['limit'] = $this->rules['limit'] . ',limit,' . $id;
    }
}