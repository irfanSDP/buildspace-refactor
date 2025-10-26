<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorManagementUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_management_users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('group_identifier');
			$table->unsignedInteger('user_id');
			$table->boolean('is_admin')->default(false);
			$table->timestamps();

			$table->unique(array('group_identifier', 'user_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_management_users');
	}

}
