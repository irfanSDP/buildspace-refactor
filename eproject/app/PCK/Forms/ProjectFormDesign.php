<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class ProjectFormDesign extends FormValidator {

    /**
     * Validation rules for creating or updating Project
     *
     * @var array
     */
    protected $rules = [
        'title'                       => 'required',
        'reference'                   => 'required|unique:projects,reference,NULL,id,deleted_at,NULL',
        'running_number'              => 'required|integer|min:1',
        'address'                     => 'required',
        'description'                 => 'required',
        'contract_id'                 => 'required|integer|exists:contracts,id',
        'country_id'                  => 'required|integer|exists:countries,id',
        'state_id'                    => 'required|integer|exists:states,id',
        'subsidiary_id'               => 'required|integer|exists:subsidiaries,id',
        'work_category_id'            => 'required|integer|exists:work_categories,id',
        'letter_of_award_template_id' => 'required|integer|exists:letter_of_awards,id',
        'form_of_tender_template_id'  => 'required|integer|exists:form_of_tenders,id',
    ];

    protected $messages = [
        'reference.unique'                     => 'There is already a project with this Contract No. Please enter another one, or refresh the page to generate one automatically.',
        'running_number.integer'               => 'The contract number must be an integer.',
        'running_number.min'                   => 'The contract number must be greater than 0.',
        'subsidiary_id.required'               => 'Please select a subsidiary.',
        'subsidiary_id.exists'                 => 'The selected subsidiary does not exist.',
        'work_category_id.exists'              => 'The selected work category does not exist.',
        'contract_id.exists'                   => 'The selected contract does not exist.',
        'letter_of_award_template_id.required' => 'Letter of Award\'s template must be selected',
        'form_of_tender_template_id.required'  => 'Form of Tender\'s template must be selected',
    ];

}