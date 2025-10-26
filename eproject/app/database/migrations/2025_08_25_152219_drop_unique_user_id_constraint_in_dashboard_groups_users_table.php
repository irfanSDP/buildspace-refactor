<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUniqueUserIdConstraintInDashboardGroupsUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('dashboard_groups_users', function(Blueprint $table)
		{
            DB::statement('ALTER TABLE dashboard_groups_users DROP CONSTRAINT IF EXISTS dashboard_groups_users_user_id_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        // Do nothing
	}

}
