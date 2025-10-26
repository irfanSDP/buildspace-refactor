<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\Companies\Company;

class AddCompanyStatusColumnToRelatedTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->integer('company_status')->nullable();
		});

		DB::statement("UPDATE companies SET company_status = " . Company::COMPANY_STATUS_BUMIPUTERA . " WHERE is_bumiputera IS TRUE;");
		DB::statement("UPDATE companies SET company_status = " . Company::COMPANY_STATUS_NON_BUMIPUTERA . " WHERE is_bumiputera IS FALSE;");

		Schema::table('company_temporary_details', function(Blueprint $table)
		{
			$table->integer('company_status')->nullable();
		});

		DB::statement("UPDATE company_temporary_details SET company_status = " . Company::COMPANY_STATUS_BUMIPUTERA . " WHERE is_bumiputera IS TRUE;");
		DB::statement("UPDATE company_temporary_details SET company_status = " . Company::COMPANY_STATUS_NON_BUMIPUTERA . " WHERE is_bumiputera IS FALSE;");

		Schema::table('company_detail_attachment_settings', function(Blueprint $table) {
			$table->renameColumn('bumiputera_status_attachments', 'company_status_attachments');
		});

		DB::statement("UPDATE object_fields SET field = 'vendorRegistrationDetailsCompanyStatus' WHERE field = 'vendorRegistrationDetailsCompanyBumiputeraStatus';");

		Schema::table('vendor_detail_settings', function(Blueprint $table) {
			$table->renameColumn('is_bumiputera_instructions', 'company_status_instructions');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->dropColumn('company_status');
		});

		Schema::table('company_temporary_details', function(Blueprint $table)
		{
			$table->dropColumn('company_status');
		});

		Schema::table('company_detail_attachment_settings', function(Blueprint $table) {
			$table->renameColumn('company_status_attachments', 'bumiputera_status_attachments');
		});

		DB::statement("UPDATE object_fields SET field = 'vendorRegistrationDetailsCompanyBumiputeraStatus' WHERE field = 'vendorRegistrationDetailsCompanyStatus';");

		Schema::table('vendor_detail_settings', function(Blueprint $table) {
			$table->renameColumn('company_status_instructions', 'is_bumiputera_instructions');
		});
	}
}
