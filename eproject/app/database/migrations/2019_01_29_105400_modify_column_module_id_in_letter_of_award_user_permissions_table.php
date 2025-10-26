<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ModifyColumnModuleIdInLetterOfAwardUserPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('letter_of_award_user_permissions', function(Blueprint $table)
		{
			$table->renameColumn('module_id', 'module_identifier');
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
			$table->renameColumn('module_identifier', 'module_id');
		});
	}

}
