<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTenderFormVerifierLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tender_form_verifier_logs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('loggable_id');
			$table->string('loggable_type');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('type');
			$table->timestamps();

			$table->index(array( 'loggable_id', 'loggable_type', 'user_id' ));

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
		Schema::drop('tender_form_verifier_logs');
	}

}