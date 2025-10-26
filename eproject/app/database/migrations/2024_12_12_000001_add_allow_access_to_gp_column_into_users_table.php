<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAllowAccessToGpColumnIntoUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function (Blueprint $table)
		{
			$table->boolean('allow_access_to_gp')->default(false);
			$table->string('gp_access_token')->nullable();

			$table->index('allow_access_to_gp');
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
			$table->dropColumn('allow_access_to_gp');
			$table->dropColumn('gp_access_token');
		});
	}

}