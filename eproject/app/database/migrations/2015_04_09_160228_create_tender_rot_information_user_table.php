<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\TenderFormVerifierLogs\FormLevelStatus;

class CreateTenderRotInformationUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tender_rot_information_user', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_rot_information_id');
			$table->unsignedInteger('user_id');
			$table->smallInteger('status', false, true)->index()->default(FormLevelStatus::USER_VERIFICATION_IN_PROGRESS);
			$table->timestamps();

			$table->foreign('tender_rot_information_id')->references('id')->on('tender_rot_information');
			$table->foreign('user_id')->references('id')->on('users');

			$table->index(array( 'tender_rot_information_id', 'user_id', 'status' ), 'tender_rot_verifier_idx');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tender_rot_information_user');
	}

}
