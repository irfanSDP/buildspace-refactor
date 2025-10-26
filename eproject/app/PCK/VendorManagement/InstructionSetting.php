<?php namespace PCK\VendorManagement;

use Illuminate\Database\Eloquent\Model;

class InstructionSetting extends Model {

    protected $table = 'vendor_management_instruction_settings';

    protected $fillable = [
        'company_personnel',
        'project_track_record',
        'supplier_credit_facilities',
        'payment',
        'vendor_pre_qualifications',
    ];
}