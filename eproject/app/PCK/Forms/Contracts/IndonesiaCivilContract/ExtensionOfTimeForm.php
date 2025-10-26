<?php namespace PCK\Forms\Contracts\IndonesiaCivilContract;

use Laracasts\Validation\FormValidationException;
use Laracasts\Validation\FormValidator;
use PCK\IndonesiaCivilContract\ExtensionOfTime\ExtensionOfTime;
use PCK\Projects\Project;

class ExtensionOfTimeForm extends FormValidator {

    private $project;
    private $model;

    public function getValidationRules()
    {
        $modelId   = $this->model->id ?? 'NULL';
        $projectId = $this->project->id ?? 'NULL';

        return [
            'indonesia_civil_contract_ai_id' => 'integer',
            'reference'                      => array(
                'required' => 'required',
                'max'      => 'max:200',
                'unique'   => 'unique:indonesia_civil_contract_extensions_of_time,reference,' . $modelId . ',id,project_id,' . $projectId,
            ),
            'subject'                        => 'required|max:200',
            'details'                        => 'required',
            'selected_clauses'               => 'required|array',
            'early_warnings'                 => 'required|array',
            'days'                           => 'required|integer|min:0',
        ];
    }

    public function validate($formData)
    {
        if( $this->model && $this->model->status != ExtensionOfTime::STATUS_DRAFT ) throw new FormValidationException('Unable to update the form in the current state.', array());

        parent::validate($formData);
    }

    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

}