<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRequestForVariationUserPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_for_variation_user_permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('project_id');
			$table->integer('user_id');
			$table->integer('module_id');
			$table->boolean('is_editor')->default(false);
			$table->integer('added_by');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('request_for_variation_user_permissions');
	}

}
