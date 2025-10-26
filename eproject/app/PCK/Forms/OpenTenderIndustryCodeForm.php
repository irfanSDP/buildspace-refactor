<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;

class OpenTenderIndustryCodeForm extends FormValidator{


	protected $rules = 
    [
        'vendor_category_id'  => 'required',
        'vendor_work_category_id'  => 'required',
        'cidb_code_id'        => 'required',
        'cidb_grade_id'       => 'required'
    ];

    protected $messages = 
    [
        'vendor_category_id.required'  => 'The vendor_category_id field is required.',
        'vendor_work_category_id.required'  => 'The vendor_work_category_id field is required.',
        'cidb_code_id.required'        => 'The cidb_code_id field is required.',
        'cidb_grade_id.required'       => 'The cidb_grade_id field is required.',
    ];

}