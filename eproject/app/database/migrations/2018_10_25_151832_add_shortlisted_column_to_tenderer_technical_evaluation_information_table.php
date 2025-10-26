<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddShortlistedColumnToTendererTechnicalEvaluationInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tenderer_technical_evaluation_information', function(Blueprint $table)
		{
			$table->boolean('shortlisted')->default(false);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tenderer_technical_evaluation_information', function(Blueprint $table)
		{
			$table->dropColumn('shortlisted');
		});
	}

}
