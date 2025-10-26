<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubmittedByColumnToProjectLabourRatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('project_labour_rates', function (Blueprint $table)
		{
			$table->unsignedInteger('submitted_by')->nullable();
			$table->foreign('submitted_by')->references('id')->on('users');
			
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('project_labour_rates', function (Blueprint $table)
		{
			$table->dropColumn('submitted_by');
		});
	}

}
