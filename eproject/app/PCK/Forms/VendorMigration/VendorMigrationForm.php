<?php namespace PCK\Forms\VendorMigration;

use Illuminate\Support\MessageBag;
use Laracasts\Validation\FormValidator;

class VendorMigrationForm extends FormValidator
{
    protected $rules = [
        'ids'           => 'required|array',
        'vendorGroupId' => 'required',
    ];

    protected $messages = [
        'ids.required'           => 'At least 1 vendor(s) must be selected.',
        'vendorGroupId.required' => 'Vendor group must be selected.',
    ];
}