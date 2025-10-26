<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionGroupUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspection_group_users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('inspection_role_id');
			$table->unsignedInteger('inspection_group_id');
			$table->unsignedInteger('user_id');

			$table->foreign('inspection_role_id')->references('id')->on('inspection_roles')->onDelete('cascade');
			$table->foreign('inspection_group_id')->references('id')->on('inspection_groups')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

			$table->index('user_id');
			$table->index(array('inspection_role_id', 'inspection_group_id', 'user_id'), 'inspection_group_users_idx');
			$table->unique(array('inspection_group_id', 'user_id'), 'inspection_group_users_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspection_group_users');
	}

}
