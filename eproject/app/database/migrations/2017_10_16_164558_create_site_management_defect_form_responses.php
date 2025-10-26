<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteManagementDefectFormResponses extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_defect_form_responses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('remark');
			$table->string('path_to_photo')->nullable();
			$table->unsignedInteger('response_identifier');
			$table->unsignedInteger('site_management_defect_id')->index();
			$table->unsignedInteger('user_id')->index();  
			$table->timestamps();

			$table->foreign('site_management_defect_id')->references('id')->on('site_management_defects');
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
		Schema::drop('site_management_defect_form_responses');
	}

}
