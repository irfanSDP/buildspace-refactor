<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLetterOfAwardUserPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('letter_of_award_user_permissions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('module_id');
			$table->boolean('is_editor')->default(false);
            $table->unsignedInteger('added_by');
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
		Schema::drop('letter_of_award_user_permissions');
	}

}
