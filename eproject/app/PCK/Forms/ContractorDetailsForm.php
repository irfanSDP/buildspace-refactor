<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ContractorDetailsForm extends FormValidator{

    /**
     * Validation rules for creating a new Contractor record
     *
     * @var array
     */
    protected $rules = [
        'work_category'             => 'required',
        'work_subcategory'          => 'required',
        'current_cpe_grade_id'      => 'required',
        'previous_cpe_grade_id'     => 'required',
        'registration_status_id'    => 'required',
        'job_limit_number'          => 'integer|min:0|digits_between:0,19',
        'registered_date'           => 'date'
    ];

}