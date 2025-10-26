<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterNameColumnChangeTypeToTextInTechnicalEvaluationItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('technical_evaluation_items', function(Blueprint $table)
		{
			\DB::statement('ALTER TABLE technical_evaluation_items ALTER column name TYPE TEXT');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('technical_evaluation_items', function(Blueprint $table)
		{
			\DB::statement('ALTER TABLE technical_evaluation_items ALTER column name TYPE VARCHAR(255)');
		});
	}

}
