<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ContractGroupCategoryForm extends FormValidator {

    /**
     * Validation rules for creating a new Resource
     *
     * @var array
     */
    protected $rules = [
        'name' => array(
            'required',
            'min:3',
            'max:50',
            'uniquenessRule' => 'unique:contract_group_categories'
        ),
    ];

    public function ignoreUnique($id)
    {
        $this->rules['name']['uniquenessRule'] = $this->rules['name']['uniquenessRule'] . ',name,' . $id;
    }

}