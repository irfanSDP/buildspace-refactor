<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnsuccessfulAtColumnToVendorRegistrationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registrations', function(Blueprint $table)
		{
			$table->timestamp('unsuccessful_at')->nullable();
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
			$table->dropColumn('unsuccessful_at');
		});
	}

}
