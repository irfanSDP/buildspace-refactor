<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPerformanceEvaluationRemovalRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_performance_evaluation_removal_requests', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('vendor_performance_evaluation_id');
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('vendor_performance_evaluation_project_removal_reason_id')->nullable();
			$table->text('vendor_performance_evaluation_project_removal_reason_text')->nullable();
			$table->boolean('evaluation_removed')->default(false);
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->foreign('vendor_performance_evaluation_id', 'vendor_performance_evaluation_removal_requests_evaluation_fk')->references('id')->on('vendor_performance_evaluations')->onDelete('cascade');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('vendor_performance_evaluation_project_removal_reason_id', 'vendor_performance_evaluation_removal_requests_reason_fk')->references('id')->on('vendor_performance_evaluation_project_removal_reasons')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_performance_evaluation_removal_requests');
	}

}
