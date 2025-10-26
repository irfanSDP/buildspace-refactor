<?php namespace PCK\Forms;

use PCK\Inspections\InspectionRole;

class InspectionRoleForm extends CustomFormValidator {

    protected $throwException = false;

    protected function setRules($formData)
    {
        if( isset($formData['name']) )
        {
            $this->rules['name'] = 'required|min:1|max:250';
        }
    }

    public function postParentValidation($formData)
    {
        $messageBag = $this->getNewMessageBag();

        if( isset($formData['name']) )
        {
            $roleExists = ! InspectionRole::where('project_id', '=', $formData['project_id'])
                ->where('name', '=', $formData['name'])
                ->get()
                ->isEmpty();

            if( $roleExists )
            {
                $messageBag->add('name', trans('inspection.inspectionRoleExists'));
            }
        }

        return $messageBag;
    }
}