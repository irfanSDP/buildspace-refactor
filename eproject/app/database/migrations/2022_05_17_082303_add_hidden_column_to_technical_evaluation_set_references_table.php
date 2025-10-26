<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHiddenColumnToTechnicalEvaluationSetReferencesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('technical_evaluation_set_references', function(Blueprint $table)
		{
			$table->boolean('hidden')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('technical_evaluation_set_references', function(Blueprint $table)
		{
			$table->dropColumn('hidden');
		});
	}

}
