<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMcarFormResponsesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_mcar_form_responses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('cause');
			$table->string('action');
			$table->string('corrective');
			$table->string('comment')->default('none');
			$table->unsignedInteger('submitted_user_id')->index();
			$table->unsignedInteger('site_management_defect_id')->index();
			$table->timestamps();

			$table->foreign('site_management_defect_id')->references('id')->on('site_management_defects');
			$table->foreign('submitted_user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('site_management_mcar_form_responses');
	}

}
