<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEngineerInstructionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('engineer_instructions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('detailed_elaborations');
			$table->date('deadline_to_comply_with');
			$table->smallInteger('status', false, true);
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
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
		Schema::drop('engineer_instructions');
	}

}