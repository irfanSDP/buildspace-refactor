<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveNotNullConstraintFromTemplateNodeIdColumnInVendorPerformanceEvaluationSetupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('ALTER TABLE vendor_performance_evaluation_setups ALTER COLUMN template_node_id DROP NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('ALTER TABLE vendor_performance_evaluation_setups ALTER COLUMN template_node_id SET NOT NULL');
	}

}
