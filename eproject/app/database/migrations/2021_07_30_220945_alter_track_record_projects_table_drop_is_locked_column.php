<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterTrackRecordProjectsTableDropIsLockedColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('track_record_projects', function(Blueprint $table)
		{
			$table->dropColumn('is_locked');
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
			$table->boolean('is_locked')->default(false);
		});
	}

}
