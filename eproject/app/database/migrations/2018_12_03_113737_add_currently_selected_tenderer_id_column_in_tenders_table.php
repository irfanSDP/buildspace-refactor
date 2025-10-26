<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCurrentlySelectedTendererIdColumnInTendersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tenders', function(Blueprint $table)
		{
			$table->unsignedInteger('currently_selected_tenderer_id')->nullable();
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
			$table->dropColumn('currently_selected_tenderer_id');
		});
	}

}
