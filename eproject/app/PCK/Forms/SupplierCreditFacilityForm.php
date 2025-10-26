<?php namespace PCK\Forms;

class SupplierCreditFacilityForm extends CustomFormValidator {

    protected $rules = [
        'supplier_name'     => 'required|max:250',
        'credit_facilities' => 'required|max:250',
    ];
}
