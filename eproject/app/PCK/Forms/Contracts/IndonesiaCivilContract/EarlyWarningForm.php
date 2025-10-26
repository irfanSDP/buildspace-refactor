<?php namespace PCK\Forms\Contracts\IndonesiaCivilContract;

use Laracasts\Validation\FormValidator;
use PCK\Projects\Project;

class EarlyWarningForm extends FormValidator {

    private $project;
    private $model;

    public function getValidationRules()
    {
        $modelId   = $this->model->id ?? 'NULL';
        $projectId = $this->project->id ?? 'NULL';

        return [
            'reference'         => array(
                'required' => 'required',
                'max'      => 'max:200',
                'unique'   => 'unique:indonesia_civil_contract_early_warnings,reference,' . $modelId . ',id,project_id,' . $projectId,
            ),
            'impact'            => 'required',
            'commencement_date' => "required|date",
        ];
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