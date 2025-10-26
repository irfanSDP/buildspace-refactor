<?php namespace PCK\Forms\Contracts\IndonesiaCivilContract;

use Carbon\Carbon;
use Laracasts\Validation\FormValidationException;
use Laracasts\Validation\FormValidator;
use PCK\IndonesiaCivilContract\ArchitectInstruction\ArchitectInstruction;
use PCK\Projects\Project;

class AddNewArchitectInstructionForm extends FormValidator {

    private $project;
    private $model;

    public function getValidationRules()
    {
        $lastReturnDates = Carbon::now()->format(\Config::get('dates.submission_date_formatting'));

        $modelId   = $this->model->id ?? 'NULL';
        $projectId = $this->project->id ?? 'NULL';

        return [
            'reference'          => array(
                'required' => 'required',
                'max'      => 'max:200',
                'unique'   => 'unique:indonesia_civil_contract_architect_instructions,reference,' . $modelId . ',id,project_id,' . $projectId,
            ),
            'instruction'        => 'required',
            'rfi'                => 'array',
            'deadline_to_comply' => "date|after:{$lastReturnDates}",
        ];
    }

    public function validate($formData)
    {
        if( $this->model && ( ( $this->model->status != ArchitectInstruction::STATUS_DRAFT ) ) ) throw new FormValidationException('Unable to update the form in the current state.', array());

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