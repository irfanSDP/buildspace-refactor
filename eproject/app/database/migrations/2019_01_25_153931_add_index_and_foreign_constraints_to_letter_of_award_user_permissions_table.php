<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndexAndForeignConstraintsToLetterOfAwardUserPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('letter_of_award_user_permissions', function(Blueprint $table)
		{
			$table->index('project_id');
			$table->index('user_id');

			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('letter_of_award_user_permissions', function(Blueprint $table)
		{
			$table->dropIndex('letter_of_award_user_permissions_project_id_index');
			$table->dropIndex('letter_of_award_user_permissions_user_id_index');

			$table->dropForeign('letter_of_award_user_permissions_project_id_foreign');
			$table->dropForeign('letter_of_award_user_permissions_user_id_foreign');
		});
	}

}
