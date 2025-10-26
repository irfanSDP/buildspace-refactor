<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectReportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_reports', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->nullable();
			$table->unsignedInteger('root_id')->nullable();
			$table->unsignedInteger('origin_id')->nullable();
			$table->string('title');
			$table->integer('revision');
			$table->unsignedInteger('submitted_by')->nullable();
			$table->integer('status');
			$table->timestamps();

			$table->softDeletes();

			$table->index('project_id');
			$table->index('origin_id');
			$table->index('submitted_by');

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('origin_id')->references('id')->on('project_reports');
			$table->foreign('submitted_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('project_reports');
	}

}
