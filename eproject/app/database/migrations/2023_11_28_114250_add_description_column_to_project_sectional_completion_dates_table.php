<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDescriptionColumnToProjectSectionalCompletionDatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('project_sectional_completion_dates', function(Blueprint $table)
		{
			$table->text('description')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('project_sectional_completion_dates', function(Blueprint $table)
		{
			$table->dropColumn('description');
		});
	}

}
