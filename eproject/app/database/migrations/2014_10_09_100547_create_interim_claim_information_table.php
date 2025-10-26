<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInterimClaimInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('interim_claim_informations', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('interim_claim_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('reference');
			$table->date('date');
			$table->decimal('nett_addition_omission', 19, 2);
			$table->date('date_of_certificate')->nullable();
			$table->decimal('net_amount_of_payment_certified', 19, 2)->default(0);
			$table->decimal('gross_values_of_works', 19, 2);
			$table->string('amount_in_word');
			$table->smallInteger('type', false, true)->index();
			$table->timestamps();

			$table->foreign('interim_claim_id')->references('id')->on('interim_claims');
			$table->foreign('created_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('interim_claim_informations');
	}

}