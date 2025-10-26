<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;
use PCK\RequestForInspection\RequestForInspectionInspection;

class RequestForInspectionReplyForm extends FormValidator {

    protected $rules = [
        'comments'        => 'required|max:200',
        'contract_groups' => 'required|array|arrayNotEmpty',
        'verifiers'       => 'array',
    ];

    protected $messages = [
        'contract_groups.required' => 'Please select at least one party to request information from.',
    ];

    private $inspection;

    public function setParameters(RequestForInspectionInspection $inspection)
    {
        $this->inspection = $inspection;
    }

    public function validate($formData)
    {
        switch($this->inspection->status)
        {
            case RequestForInspectionInspection::STATUS_REMEDY_WITH_RE_INSPECTION:
                $this->rules['ready_date'] = 'required|date';
                break;
            case RequestForInspectionInspection::STATUS_REMEDY_WITHOUT_RE_INSPECTION:
                $this->rules['completed_date'] = 'required|date';
                break;
            default:
                throw new \Exception('Invalid inspection status');
        }

        parent::validate($formData);
    }

}