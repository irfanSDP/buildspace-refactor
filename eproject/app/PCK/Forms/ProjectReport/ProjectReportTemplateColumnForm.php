<?php namespace PCK\Forms\ProjectReport;

use Illuminate\Support\MessageBag;
use PCK\Exceptions\ValidationException;
use PCK\Forms\CustomFormValidator;
use PCK\ProjectReport\ProjectReportColumn;

class ProjectReportTemplateColumnForm extends CustomFormValidator
{
    protected function setRules($formData)
    {
        $this->rules['columnType'] = 'required';

        if(in_array($formData['columnType'], [ProjectReportColumn::COLUMN_CUSTOM, ProjectReportColumn::COLUMN_GROUP]))
        {
            $this->rules['title'] = 'required|min:1|max:200';
        }
    
        $this->messages['title.required']      = 'Column title is required.';
        $this->messages['columnType.required'] = 'Column type is required.';
    }
}
