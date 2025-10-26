<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateColumnSubmittedForApprovalByInRequestForVariationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request_for_variations', function(Blueprint $table)
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
		Schema::table('request_for_variations', function(Blueprint $table)
		{
			$table->dropColumn('submitted_by');
		});
	}

}
