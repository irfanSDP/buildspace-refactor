<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClaimCertificateInvoiceInformationUpdateLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('claim_certificate_invoice_information_update_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('claim_certificate_invoice_information_id')->index();
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('claim_certificate_invoice_information_update_logs');
	}

}
