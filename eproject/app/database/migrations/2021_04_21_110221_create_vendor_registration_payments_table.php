<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorRegistrationPaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_registration_payments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('payment_setting_id');
			$table->integer('running_number');
			$table->boolean('currently_selected')->default(false);
			$table->boolean('submitted')->default(false);
			$table->boolean('paid')->default(false);
			$table->boolean('successful')->default(false);
			$table->timestamps();

			$table->index('company_id');
			$table->index('payment_setting_id');

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->foreign('payment_setting_id')->references('id')->on('payment_settings')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_registration_payments');
	}

}
