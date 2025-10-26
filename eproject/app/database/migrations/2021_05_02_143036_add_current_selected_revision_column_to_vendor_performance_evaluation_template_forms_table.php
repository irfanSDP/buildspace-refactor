<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrentSelectedRevisionColumnToVendorPerformanceEvaluationTemplateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_template_forms', function(Blueprint $table)
		{
			$table->boolean('current_selected_revision')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_template_forms', function(Blueprint $table)
		{
			$table->dropColumn('current_selected_revision');
		});
	}

}
