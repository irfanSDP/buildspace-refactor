<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddendumChecklistColumnToProjectProgressChecklistsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('project_progress_checklists', function (Blueprint $table)
		{
            $table->boolean('skip_project_addendum_finalised')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('project_progress_checklists', function (Blueprint $table)
		{
			$table->dropColumn('skip_project_addendum_finalised');
		});
	}

}
