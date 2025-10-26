<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPerformanceEvaluationFormChangeRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluation_form_change_requests', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('vendor_performance_evaluation_setup_id');
			$table->text('remarks')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->index('vendor_performance_evaluation_setup_id', 'vpe_form_change_request_setup_id_idx');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('vendor_performance_evaluation_setup_id', 'vpe_form_change_requests_setup_id_fk')->references('id')->on('vendor_performance_evaluation_setups')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluation_form_change_requests');
	}

}
