<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;
use PCK\SiteManagement\SiteManagementMCAR;

class AddNewSiteManagementMCARForm extends FormValidator{

	protected $rules = 
    [   'mcar_number'	   => 'required|unique:site_management_mcar',
        'remark'           => 'required',
        'work_description' => 'required',
    ];
}