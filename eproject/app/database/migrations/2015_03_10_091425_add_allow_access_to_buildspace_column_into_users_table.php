<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddAllowAccessToBuildspaceColumnIntoUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function (Blueprint $table)
		{
			$table->boolean('allow_access_to_buildspace')->default(false);

			$table->index('allow_access_to_buildspace');
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
			$table->dropColumn('allow_access_to_buildspace');
		});
	}

}
