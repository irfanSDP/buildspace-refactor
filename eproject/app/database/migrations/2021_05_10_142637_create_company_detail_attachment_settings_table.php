<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\VendorDetailSetting\VendorDetailAttachmentSetting;

class CreateCompanyDetailAttachmentSettingsTable extends Migration
{
	public function up()
	{
		Schema::create('company_detail_attachment_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('name_attachments')->default(false);
			$table->boolean('address_attachments')->default(false);
			$table->boolean('contract_group_category_attachments')->default(false);
			$table->boolean('vendor_category_attachments')->default(false);

			$table->boolean('main_contact_attachments')->default(false);
			$table->boolean('reference_number_attachments')->default(false);
			$table->boolean('tax_registration_number_attachments')->default(false);
			$table->boolean('email_attachments')->default(false);
			$table->boolean('telephone_attachments')->default(false);
			$table->boolean('fax_attachments')->default(false);
			$table->boolean('country_attachments')->default(false);
			$table->boolean('state_attachments')->default(false);

			$table->boolean('bumiputera_status_attachments')->default(false);
			$table->boolean('bumiputera_equity_attachments')->default(false);
			$table->boolean('non_bumiputera_equity_attachments')->default(false);
			$table->boolean('foreigner_equity_attachments')->default(false);
			$table->timestamps();
		});

		if(is_null(VendorDetailAttachmentSetting::first()))
		{
			$record 						 			 = new VendorDetailAttachmentSetting();
			$record->name_attachments 					 = false;
			$record->address_attachments 				 = false;
			$record->contract_group_category_attachments = false;
			$record->vendor_category_attachments 		 = false;
			$record->main_contact_attachments 			 = false;
			$record->reference_number_attachments 		 = false;
			$record->tax_registration_number_attachments = false;
			$record->email_attachments 					 = false;
			$record->telephone_attachments 				 = false;
			$record->fax_attachments 					 = false;
			$record->country_attachments 				 = false;
			$record->state_attachments 					 = false;
			$record->bumiputera_status_attachments 		 = false;
			$record->bumiputera_equity_attachments 		 = false;
			$record->non_bumiputera_equity_attachments 	 = false;
			$record->foreigner_equity_attachments 		 = false;
			$record->save();
		}
	}

	public function down()
	{
		Schema::drop('company_detail_attachment_settings');
	}
}
