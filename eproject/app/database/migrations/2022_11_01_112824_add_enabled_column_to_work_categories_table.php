<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddEnabledColumnToWorkCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('work_categories', function(Blueprint $table)
		{
			$table->boolean('enabled')->default(true);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('work_categories', function(Blueprint $table)
		{
			$table->dropColumn('enabled');
		});
	}

}
