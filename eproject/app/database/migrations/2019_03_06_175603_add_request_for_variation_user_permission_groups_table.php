<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestForVariationUserPermissionGroupsTable extends Migration {
    /**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_for_variation_user_permission_groups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name', 100);
			$table->unsignedInteger('project_id')->index();  
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('request_for_variation_user_permission_groups');
	}

}
