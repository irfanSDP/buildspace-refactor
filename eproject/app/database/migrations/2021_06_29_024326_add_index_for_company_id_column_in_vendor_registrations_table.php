<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexForCompanyIdColumnInVendorRegistrationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registrations', function(Blueprint $table)
		{
			$table->index('company_id', 'vendor_registrations_company_id_idx');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_registrations', function(Blueprint $table)
		{
			$table->dropIndex('vendor_registrations_company_id_idx');
		});
	}

}
