<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddRemarksColumnToTechnicalEvaluationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('technical_evaluations', function(Blueprint $table)
		{
			$table->text('remarks')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('technical_evaluations', function(Blueprint $table)
		{
			$table->dropColumn('remarks');
		});
	}

}
