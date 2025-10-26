<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorPerformanceEvaluationProcessorEditDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluation_processor_edit_details', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_performance_evaluation_processor_edit_log_id');
			$table->unsignedInteger('weighted_node_id');
			$table->unsignedInteger('previous_score_id')->nullable();
			$table->boolean('is_previous_node_excluded')->default(false);
			$table->unsignedInteger('current_score_id')->nullable();
			$table->boolean('is_current_node_excluded')->default(false);
			$table->timestamps();

			$table->index('vendor_performance_evaluation_processor_edit_log_id');
			$table->index('weighted_node_id');
			$table->index('previous_score_id');
			$table->index('current_score_id');

			$table->foreign('vendor_performance_evaluation_processor_edit_log_id')->references('id')->on('vendor_performance_evaluation_processor_edit_logs')->onDelete('cascade');
			$table->foreign('weighted_node_id')->references('id')->on('weighted_nodes')->onDelete('cascade');
			$table->foreign('previous_score_id')->references('id')->on('weighted_node_scores')->onDelete('cascade');
			$table->foreign('current_score_id')->references('id')->on('weighted_node_scores')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluation_processor_edit_details');
	}

}
