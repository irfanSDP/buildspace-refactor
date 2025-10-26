<?php namespace PCK\Forms;

use PCK\Inspections\InspectionGroup;

class InspectionGroupForm extends CustomFormValidator {

    protected $rules = [
        'name' => 'required|min:1|max:250',
    ];

    protected $throwException = false;

    public function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        $groupExists = ! InspectionGroup::where('project_id', '=', $formData['project_id'])
            ->where('name', '=', $formData['name'])
            ->get()
            ->isEmpty();

        if( $groupExists )
        {
            $messageBag->add('name', trans('inspection.inspectionGroupExists'));
        }

        return $messageBag;
    }
}