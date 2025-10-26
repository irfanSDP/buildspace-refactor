<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterTrackRecordProjectsTableAddShassicScoreColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->decimal('shassic_score', 5, 2)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->dropColumn('shassic_score');
		});
	}

}
