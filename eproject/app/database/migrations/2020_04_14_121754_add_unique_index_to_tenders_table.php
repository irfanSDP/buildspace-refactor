<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexToTendersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tenders', function(Blueprint $table)
		{
			$table->unique(array('project_id', 'count'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tenders', function(Blueprint $table)
		{
			$table->dropUnique('tenders_project_id_count_unique');
		});
	}

}
