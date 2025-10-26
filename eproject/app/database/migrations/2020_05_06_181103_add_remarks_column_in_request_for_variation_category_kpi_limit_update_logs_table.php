<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddRemarksColumnInRequestForVariationCategoryKpiLimitUpdateLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request_for_variation_category_kpi_limit_update_logs', function(Blueprint $table)
		{
			$table->text('remarks')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('request_for_variation_category_kpi_limit_update_logs', function(Blueprint $table)
		{
			$table->dropColumn('remarks');
		});
	}

}
