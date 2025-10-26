<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class AddNewSiteManagementSiteDiaryLabourForm extends FormValidator{


	protected $rules = 
    [
        'labour_project_manager'           => 'required',
        'labour_site_agent'                => 'required',
        'labour_supervisor'                => 'required',
    ];

    protected $messages = 
    [
        'labour_project_manager.required' => 'The project manager field is required.',
        'labour_site_agent.required'  => 'The site agent field is required.',
        'labour_supervisor.required'  => 'The supervisor field is required.'
    ];

}