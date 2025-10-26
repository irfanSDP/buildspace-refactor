<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedByAndUpdatedByColumnsToVendorPerformanceEvaluationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluations', function(Blueprint $table)
		{
			$table->unsignedInteger('created_by')->nullable();
			$table->unsignedInteger('updated_by')->nullable();
			$table->unsignedInteger('deleted_by')->nullable();

			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluations', function(Blueprint $table)
		{
			$table->dropColumn('created_by');
			$table->dropColumn('updated_by');
			$table->dropColumn('deleted_by');
		});
	}

}
