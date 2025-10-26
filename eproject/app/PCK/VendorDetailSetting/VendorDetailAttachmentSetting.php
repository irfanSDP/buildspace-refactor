<?php namespace PCK\VendorDetailSetting;

use Illuminate\Database\Eloquent\Model;

class VendorDetailAttachmentSetting extends Model
{
    protected $table = 'company_detail_attachment_settings';

    public static function updateSetting($inputs)
    {
        $record 						             = self::first();
        $record->name_attachments 					 = isset($inputs['name_attachments']);
        $record->address_attachments 				 = isset($inputs['address_attachments']);
        $record->contract_group_category_attachments = isset($inputs['contract_group_category_attachments']);
        $record->vendor_category_attachments 		 = isset($inputs['vendor_category_attachments']);
        $record->main_contact_attachments 			 = isset($inputs['main_contact_attachments']);
        $record->reference_number_attachments 		 = isset($inputs['reference_number_attachments']);
        $record->tax_registration_number_attachments = isset($inputs['tax_registration_number_attachments']);
        $record->email_attachments 					 = isset($inputs['email_attachments']);
        $record->telephone_attachments 				 = isset($inputs['telephone_attachments']);
        $record->fax_attachments 					 = isset($inputs['fax_attachments']);
        $record->country_attachments 				 = isset($inputs['country_attachments']);
        $record->state_attachments 					 = isset($inputs['state_attachments']);
        $record->company_status_attachments 		 = isset($inputs['company_status_attachments']);
        $record->bumiputera_equity_attachments 		 = isset($inputs['bumiputera_equity_attachments']);
        $record->non_bumiputera_equity_attachments 	 = isset($inputs['non_bumiputera_equity_attachments']);
        $record->foreigner_equity_attachments 		 = isset($inputs['foreigner_equity_attachments']);
        $record->cidb_grade_attachments              = isset($inputs['cidb_grade_attachments']);
        $record->bim_level_attachments               = isset($inputs['bim_level_attachments']);
        $record->save();

        return self::find($record->id);
    }
}