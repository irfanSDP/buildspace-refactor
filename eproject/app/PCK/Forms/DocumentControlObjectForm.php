<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\Projects\Project;

class DocumentControlObjectForm extends FormValidator {

    protected $rules = [
        'subject'          => array(
            'required',
            'max:100',
        ),
        'reference_number' => array(
            'integer',
            'min:1',
        ),
    ];

    private $project;
    private $messageType;

    public function setParameters(Project $project, $messageType)
    {
        $this->project     = $project;
        $this->messageType = $messageType;
    }

    public function validate($formData)
    {
        if( $formData['reference_number'] )
        {
            array_push($this->rules['reference_number'], 'unique:document_control_objects,reference_number,NULL,id,project_id,' . $this->project->id . ',message_type,' . $this->messageType);
        }

        parent::validate($formData);
    }

}