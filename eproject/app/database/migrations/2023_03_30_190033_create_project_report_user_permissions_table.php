<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectReportUserPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_report_user_permissions', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('project_id');
            $table->integer('user_id');
            $table->integer('identifier');
			$table->timestamps();

            $table->index('project_id');
            $table->index('user_id');
            $table->index('identifier');

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['project_id', 'user_id', 'identifier']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('project_report_user_permissions');
	}

}
