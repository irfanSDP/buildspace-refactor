<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddColumnKpiLimitToRequestForVariationCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request_for_variation_categories', function(Blueprint $table)
		{
			$table->decimal('kpi_limit', 5, 2)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('request_for_variation_categories', function(Blueprint $table)
		{
			$table->dropColumn('kpi_limit');
		});
	}

}
