<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorEvaluationScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_evaluation_scores', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_work_category_id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('vendor_performance_evaluation_id');
			$table->decimal('score', 5, 2)->default(0);
			$table->timestamps();

			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->foreign('vendor_performance_evaluation_id')->references('id')->on('vendor_performance_evaluations')->onDelete('cascade');

			$table->unique(array('vendor_work_category_id', 'company_id', 'vendor_performance_evaluation_id'), 'vendor_evaluation_scores_unique');

			$table->index('vendor_performance_evaluation_id', 'vendor_evaluation_scores_evaluation_id_idx');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_evaluation_scores');
	}

}
