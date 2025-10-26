<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMcarTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_mcar', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('site_management_defect_id')->index();
			$table->unsignedInteger('mcar_number')->index();
			$table->integer('contractor_id')->nullable();
			$table->string('work_description');
			$table->string('remark');
			$table->unsignedInteger('submitted_user_id');
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('contractor_id')->references('id')->on('companies');
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
		Schema::drop('site_management_mcar');
	}

}
