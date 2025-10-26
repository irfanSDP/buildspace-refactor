<?php namespace PCK\Forms\ExternalApplications;

use PCK\Forms\CustomFormValidator;

use PCK\ExternalApplication\OutboundAuthorization;

class OutboundAuthorizationForm extends CustomFormValidator {
    protected function setRules($formData)
    {
        $this->rules['url'] = 'required';
        $this->rules['header_params'] = 'required';
        $this->rules['type'] = 'required';

        $this->messages['url.required'] = 'URL is required';
        $this->messages['header_params.required'] = 'Header Params is required';
        $this->messages['type.required'] = 'Type is required';

        if(array_key_exists('type', $formData))
        {
            switch($formData['type'])
            {
                case OutboundAuthorization::TYPE_BEARER_TOKEN:
                    $this->rules['token'] = 'required';
                    $this->messages['token.required'] = 'Token is required';

                    break;
                case OutboundAuthorization::TYPE_OAUTH_TWO:
                    $this->rules['header_prefix'] = 'required';
                    $this->messages['header_prefix.required'] = 'Header Prefix is required';

                    $this->rules['access_token_url'] = 'required';
                    $this->messages['access_token_url.required'] = 'Access Token URL is required';

                    $this->rules['client_id'] = 'required';
                    $this->messages['client_id.required'] = 'Client ID is required';

                    $this->rules['client_secret'] = 'required';
                    $this->messages['client_secret.required'] = 'Client Secret is required';

                    $this->rules['grant_type'] = 'required';
                    $this->messages['grant_type.required'] = 'Grant Type is required';

                    break;
            }
        }
    }
}