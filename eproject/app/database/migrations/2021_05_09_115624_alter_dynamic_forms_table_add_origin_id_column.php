<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterDynamicFormsTableAddOriginIdColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('dynamic_forms', function(Blueprint $table)
		{
			$table->unsignedInteger('origin_id')->nullable();

			$table->index('origin_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('dynamic_forms', function(Blueprint $table)
		{
			$table->dropColumn('origin_id');
		});
	}

}
