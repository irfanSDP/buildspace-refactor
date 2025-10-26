<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementSiteDiaryRejectedMaterialForm extends FormValidator{


	protected $rules = 
    [
        'rejected_material_id' => 'required',
    ];

    protected $messages = 
    [
        'rejected_material_id.required' => 'The rejected_material_id field is required.'
    ];

}