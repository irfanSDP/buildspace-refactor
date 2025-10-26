<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorRegistrationSubmissionLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_registration_submission_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_registration_id');
			$table->integer('action_type');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->index('vendor_registration_id');
			$table->index('created_by');
			$table->index('updated_by');

			$table->foreign('vendor_registration_id')->references('id')->on('vendor_registrations')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_registration_submission_logs');
	}

}
