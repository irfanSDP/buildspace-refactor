<?php namespace PCK\Forms\ExternalApplications;

use PCK\Forms\CustomFormValidator;

use PCK\ExternalApplication\ClientModule;

class ClientModuleSettingsForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['id'] = 'required|exists:external_application_client_modules,id';
        $this->rules['downstream_permission'] = 'required|in:'.ClientModule::DOWNSTREAM_PERMISSION_ALL.','.ClientModule::DOWNSTREAM_PERMISSION_CLIENT.', '.ClientModule::DOWNSTREAM_PERMISSION_DISABLED.'';

        $clientModule = ClientModule::find($formData['id']);

        if($clientModule)
        {
            $attributes = call_user_func('PCK\\ExternalApplication\\Module\\'.$clientModule->module.'::getInternalAttributes');

            $attributeValues = [];

            foreach($attributes as $key => $attribute)
            {
                $rules = [];

                if((array_key_exists('required', $attribute) && $attribute['required']))
                {
                    $rules[] = 'required';
                    $this->messages['external_attribute_'.$key.'.required'] = $attribute['name'].' is required';
                }
                
                //...add any additional validator

                if(!empty($rules))
                {
                    $this->rules['external_attribute_'.$key] = implode('|', $rules);
                }

                foreach($formData as $dataKey => $dataVal)
                {
                    if($dataKey == 'external_attribute_'.$key)
                    {
                        $attributeValues['external_attribute_'.$key] = mb_strtolower($dataVal);
                    }
                }
            }

            //this was meant to validate external attribute to be unique. But for now we allow the same external attribute to be mapped to other internal attribute
            /*foreach(array_count_values($attributeValues) as $val => $c)
            {
                if($val && $c > 1)
                {
                    $key = array_search($val, $attributeValues);
                    if(array_key_exists($key, $this->rules))
                    {
                        $this->rules[$key] .= '|array';
                        $this->messages[$key.'.array'] = 'The attribute name must be unique';
                    }
                }
            }*/
        }
    }
}