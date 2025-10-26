<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIsGpAdminColumnIntoUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function (Blueprint $table)
		{
			$table->boolean('is_gp_admin')->default(false);
			$table->index('is_gp_admin');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function (Blueprint $table)
		{
			$table->dropColumn('is_gp_admin');
		});
	}

}