<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluationCompanyForm;

class AddScoreColumnToVendorPerformanceEvaluationCompanyFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_company_forms', function(Blueprint $table)
		{
			$table->unsignedInteger('score')->nullable();
		});

		foreach(VendorPerformanceEvaluationCompanyForm::all() as $vpeCompanyForm)
		{
			$score = $vpeCompanyForm->weightedNode->getScore();

			if($score == 0)
			{
				$score = null;
			}

			DB::statement("UPDATE vendor_performance_evaluation_company_forms SET score = ? WHERE id = ?", [$score, $vpeCompanyForm->id]);
		}
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_company_forms', function(Blueprint $table)
		{
			$table->dropColumn('score');
		});
	}

}
