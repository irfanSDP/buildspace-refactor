<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;

class RequestForVariationCostEstimateImportForm extends CustomFormValidator {

    protected $allowedFileTypes = [
        'xlsx',
        'xls',
        'csv',
        'ods',
        'tsv',
    ];

    protected $rules = [
        'cost_estimates' => 'required'
    ];

    protected function setRules($formData)
    {
        $this->messages = [
            'cost_estimates.required' => trans('files.fileRequired'),
        ];
    }

    protected function postParentValidation($formData)
    {
        $errors = new MessageBag();

        if( ! $formData['cost_estimates'] ) return $errors;

        $extension = $formData['cost_estimates']->getClientOriginalExtension();

        if( ! in_array($extension, $this->allowedFileTypes) )
        {
            $errors->add('cost_estimates', trans('files.wrongFileType') . ' ' . trans('files.supportedFileTypes', array( 'fileTypes' => implode(', ', $this->allowedFileTypes))));
        }

        return $errors;
    }
}