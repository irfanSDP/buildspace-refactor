<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPerformanceEvaluationFormChangeLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluation_form_change_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('vendor_performance_evaluation_setup_id');
			$table->unsignedInteger('old_template_node_id')->nullable();
			$table->unsignedInteger('new_template_node_id')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->index('vendor_performance_evaluation_setup_id', 'vpe_form_change_logs_setup_id_idx');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('vendor_performance_evaluation_setup_id', 'vpe_form_change_logs_setup_id_fk')->references('id')->on('vendor_performance_evaluation_setups')->onDelete('cascade');
			$table->foreign('old_template_node_id', 'vpe_form_change_requests_old_node_id_fk')->references('id')->on('weighted_nodes')->onDelete('cascade');
			$table->foreign('new_template_node_id', 'vpe_form_change_requests_new_node_id_fk')->references('id')->on('weighted_nodes')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluation_form_change_logs');
	}

}
