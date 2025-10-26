<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOriginalFormIdColumnToVendorPerformanceEvaluationTemplateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_template_forms', function(Blueprint $table)
		{
			$table->unsignedInteger('original_form_id')->default(0)->index();
		});

		foreach(\PCK\VendorPerformanceEvaluation\TemplateForm::all() as $templateForm)
		{
			$templateForm->original_form_id = $templateForm->id;
			$templateForm->save();
		}
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
			$table->dropColumn('original_form_id');
		});
	}

}
