<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteManagementUserPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_user_permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('module_identifier');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('project_id');
            $table->boolean('site')->default(false);
            $table->boolean('qa_qc_client')->default(false);
            $table->boolean('pm')->default(false);
            $table->boolean('qs')->default(false);
			$table->timestamps();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->index('project_id');
            $table->unique(array( 'module_identifier', 'user_id', 'project_id' ));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('site_management_user_permissions');
	}

}
