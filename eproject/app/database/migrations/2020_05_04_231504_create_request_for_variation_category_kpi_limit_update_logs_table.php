<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRequestForVariationCategoryKpiLimitUpdateLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_for_variation_category_kpi_limit_update_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('request_for_variation_category_id');
			$table->decimal('kpi_limit', 5, 2)->nullable();
			$table->unsignedInteger('created_by');
			$table->timestamps();

			$table->index('request_for_variation_category_id');
			$table->index('created_by');

			$table->foreign('request_for_variation_category_id')->references('id')->on('request_for_variation_categories')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('request_for_variation_category_kpi_limit_update_logs');
	}

}
