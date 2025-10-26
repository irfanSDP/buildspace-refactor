<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIcInfoNettAdditionOmissionAttachmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ic_info_nett_addition_omission_attachments', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('interim_claim_information_id')->index();
			$table->unsignedInteger('upload_id')->index();
			$table->timestamps();

			$table->foreign('interim_claim_information_id')->references('id')->on('interim_claim_informations');
			$table->foreign('upload_id')->references('id')->on('uploads');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ic_info_nett_addition_omission_attachments');
	}

}