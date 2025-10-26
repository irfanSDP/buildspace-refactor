<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\TenderFormVerifierLogs\FormLevelStatus;

class CreateTenderLotInformationUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tender_lot_information_user', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_lot_information_id');
			$table->unsignedInteger('user_id');
			$table->smallInteger('status', false, true)->index()->default(FormLevelStatus::USER_VERIFICATION_IN_PROGRESS);
			$table->timestamps();

			$table->foreign('tender_lot_information_id')->references('id')->on('tender_lot_information');
			$table->foreign('user_id')->references('id')->on('users');

			$table->index(array( 'tender_lot_information_id', 'user_id', 'status' ), 'tender_lot_verifier_idx');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tender_lot_information_user');
	}

}