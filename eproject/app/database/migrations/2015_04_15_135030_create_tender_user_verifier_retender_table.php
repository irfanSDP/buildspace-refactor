<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\TenderFormVerifierLogs\FormLevelStatus;

class CreateTenderUserVerifierRetenderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tender_user_verifier_retender', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id');
			$table->unsignedInteger('user_id');
			$table->smallInteger('status', false, true)->index()->default(FormLevelStatus::USER_VERIFICATION_IN_PROGRESS);
			$table->timestamps();

			$table->foreign('tender_id')->references('id')->on('tenders');
			$table->foreign('user_id')->references('id')->on('users');

			$table->index(array( 'tender_id', 'user_id', 'status' ), 'tender_retender_verifier_idx');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tender_user_verifier_retender');
	}

}