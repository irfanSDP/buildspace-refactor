<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemovedAtAndActionByColumnsToVendorPerformanceEvaluationRemovalRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_removal_requests', function(Blueprint $table)
		{
			$table->timestamp('removed_at')->nullable();
			$table->unsignedInteger('action_by')->nullable();
			$table->text('request_remarks')->default('');
			$table->text('dismissal_remarks')->nullable();

			$table->foreign('action_by')->references('id')->on('users')->onDelete('cascade');
		});

		\DB::statement('UPDATE vendor_performance_evaluation_removal_requests
			SET removed_at = updated_at
			WHERE evaluation_removed IS TRUE');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_removal_requests', function(Blueprint $table)
		{
			$table->dropColumn('removed_at');
			$table->dropColumn('action_by');
			$table->dropColumn('request_remarks');
			$table->dropColumn('dismissal_remarks');
		});
	}

}
