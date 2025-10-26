<?php namespace PCK\Forms;

use PCK\Forms\CustomFormValidator;
use PCK\Buildspace\ItemCodeSetting;
use Illuminate\Support\MessageBag;

class ItemCodeSettingAmountForm extends CustomFormValidator {

    protected $throwException = false;
    protected $project;

    protected $rules = [
        'item_code_setting_amounts' => 'required|array'
    ];

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function postParentValidation($formData)
    {
        $errors = new MessageBag;

        $this->sameOriginCheck($errors, $formData);

        $this->totalAmountCheck($errors, $formData);

        return $errors;
    }

    protected function totalAmountCheck(&$errors, $formData)
    {
        $balance = 0;

        if($this->project->pam2006Detail)
        {
            $balance = $this->project->pam2006Detail->contract_sum;
        }

        if($this->project->indonesiaCivilContractInformation)
        {
            $balance = $this->project->postContractInformation->contract_sum;
        }

        $balance = intval($balance * 100); // To avoid complications with floating point precision.

        foreach($formData['item_code_setting_amounts'] as $amountInfo)
        {
            $balance -= intval($amountInfo['amount'] * 100);
        }

        if($balance !== 0)
        {
            $errors->add('item_code_setting_amounts', trans('accountCodes.totalAmountMustEqualContractSum'));
        }
    }

    protected function sameOriginCheck(&$errors, $formData)
    {
        $bsProjectId = $this->project->getBsProjectMainInformation()->project_structure_id;

        foreach($formData['item_code_setting_amounts'] as $amountInfo)
        {
            if(!ItemCodeSetting::where('id', '=', $amountInfo['id'])
                ->where('project_structure_id', '=', $bsProjectId)
                ->exists())
            {
                $errors->add('item_code_setting_amounts', trans('forms.anErrorOccured'));
            }
        }
    }
}