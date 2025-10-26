<?php namespace PCK\Forms\ConsultantManagement;

use PCK\Forms\CustomFormValidator;

class PhaseForm extends CustomFormValidator {

    protected $throwException = true;

    protected function setRules($formData)
    {
        $this->rules['cid'] = 'required|exists:consultant_management_contracts,id';
        $this->rules['subsidiary_id'] = 'required|exists:subsidiaries,id|unique:consultant_management_subsidiaries,subsidiary_id,'.$formData['id'].',id,consultant_management_contract_id,'.$formData['cid'];
        $this->rules['development_type_id'] = 'required|exists:development_types,id';
        $this->rules['business_case'] = 'required';
        $this->rules['gross_acreage'] = 'required|numeric|min:0';
        $this->rules['project_budget'] = 'required|numeric|min:0';
        $this->rules['total_construction_cost'] = 'required|numeric|min:0';
        $this->rules['total_landscape_cost'] = 'required|numeric|min:0';
        $this->rules['cost_per_square_feet'] = 'required|numeric|min:0';
        $this->rules['planning_permission_date']  = 'required|date';
        $this->rules['building_plan_date']  = 'required|date';
        $this->rules['launch_date']  = 'required|date';

        $this->messages['subsidiary_id.unique'] = 'Subsidiary already exists for this contract';

        if(array_key_exists('product_type', $formData) && is_array($formData['product_type']))
        {
            foreach($formData['product_type'] as $idx => $fields)
            {
                $this->rules['product_type.'.$idx.'.product_type_id'] = 'required|exists:product_types,id';
                $this->rules['product_type.'.$idx.'.number_of_unit'] = 'required|integer|min:0';
                $this->rules['product_type.'.$idx.'.lot_dimension_length'] = 'required|numeric|min:0';
                $this->rules['product_type.'.$idx.'.lot_dimension_width'] = 'required|numeric|min:0';
                $this->rules['product_type.'.$idx.'.proposed_built_up_area'] = 'required|numeric|min:0';
                $this->rules['product_type.'.$idx.'.proposed_average_selling_price'] = 'required|numeric|min:0';

                $this->messages['product_type.'.$idx.'.product_type_id.required'] = trans('general.productType').' is required';
                $this->messages['product_type.'.$idx.'.number_of_unit.required'] = trans('general.noOfUnits').' is required';
                $this->messages['product_type.'.$idx.'.lot_dimension_length.required'] = trans('general.lotSize').' is required';
                $this->messages['product_type.'.$idx.'.lot_dimension_width.required'] = trans('general.lotSize').' is required';
                $this->messages['product_type.'.$idx.'.proposed_built_up_area.required'] = trans('general.proposedBuildUpArea').' is required';
                $this->messages['product_type.'.$idx.'.proposed_average_selling_price.required'] = trans('general.proposedAverageSellingPrice').' is required';

                $this->messages['product_type.'.$idx.'.number_of_unit.integer'] = trans('general.noOfUnits').' must be an integer';
            }
        }
    }
}