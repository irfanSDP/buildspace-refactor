<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcessorIdColumnToVendorRegistrationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registrations', function(Blueprint $table)
		{
			$table->unsignedInteger('processor_id')->nullable();

			$table->foreign('processor_id')->references('id')->on('users')->onDelete('cascade');
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
			$table->dropColumn('processor_id');
		});
	}

}
