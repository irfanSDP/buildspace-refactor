<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\VendorRegistration\Payment\VendorRegistrationPayment;

class AlterVendorRegistrationPaymentsTableAddStatusColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_registration_payments', function(Blueprint $table)
		{
			$table->dateTime('submitted_date')->nullable();
			$table->dateTime('paid_date')->nullable();
			$table->dateTime('successful_date')->nullable();
			$table->integer('status')->default(VendorRegistrationPayment::STATUS_DRAFT);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_registration_payments', function(Blueprint $table)
		{
			$table->dropColumn('submitted_date');
			$table->dropColumn('paid_date');
			$table->dropColumn('successful_date');
			$table->dropColumn('status');
		});
	}

}
