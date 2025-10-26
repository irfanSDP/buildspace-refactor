<?php namespace PCK\VendorDetailSetting;

use Illuminate\Database\Eloquent\Model;

class VendorDetailSetting extends Model {

    protected $table = 'vendor_detail_settings';

    protected $fillable = [
        'name_instructions',
        'address_instructions',
        'contract_group_category_instructions',
        'vendor_category_instructions',
        'contact_person_instructions',
        'reference_number_instructions',
        'tax_registration_number_instructions',
        'email_instructions',
        'telephone_instructions',
        'fax_instructions',
        'country_instructions',
        'state_instructions',
        'company_status_instructions',
        'bumiputera_equity_instructions',
        'non_bumiputera_equity_instructions',
        'foreigner_equity_instructions',
        'cidb_grade_instructions',
        'cidb_code_instructions',
        'bim_level_instructions',
    ];
}