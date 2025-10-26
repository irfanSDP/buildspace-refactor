<?php namespace PCK\Forms;

class TrackRecordProjectForm extends CustomFormValidator {

    protected $rules = [
        'title'                       => 'required|max:250',
        'vendor_category_id'          => 'required|integer',
        'vendor_work_category_id'     => 'required|integer',
        'project_amount'              => 'required|numeric|min:0|max:99999999999999999.99',
        'country_id'                  => 'required|integer',
        'project_amount_remarks'      => 'max:250',
        'year_of_site_possession'     => 'required|date_format:Y',
        'year_of_completion'          => 'required|date_format:Y',
        'qlassic_year_of_achievement' => 'date_format:Y',
        'conquas_year_of_achievement' => 'date_format:Y',
        'year_of_recognition_awards'  => 'date_format:Y',
        'has_recognition_awards'      => 'max:250',
        'qlassic_score'               => 'max:250',
        'conquas_score'               => 'max:250',
        'awards_received'             => 'max:250',
        'shassic_score'               => 'min:1|max:100',
    ];

    protected $messages = [
        'country_id.required' => 'Currency must be selected',
        'project_amount.max' => 'Amount may not exceed 99,999,999,999,999,999.99',
    ];

    protected function setRules($formData)
    {
        if($formData['property_developer_id'] == 'others')
        {
            $this->rules['property_developer_text'] = 'required|max:250';
        }
        else
        {
            $this->rules['property_developer_id'] = 'required|integer';
        }

        if(isset($formData['has_qlassic_or_conquas_score']) && $formData['has_qlassic_or_conquas_score'])
        {
            $this->rules['uploaded_files'] = 'required|array';
        }
    }

}
