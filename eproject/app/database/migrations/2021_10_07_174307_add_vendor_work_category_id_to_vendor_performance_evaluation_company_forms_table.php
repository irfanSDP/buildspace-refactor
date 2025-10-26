<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorWorkCategoryIdToVendorPerformanceEvaluationCompanyFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_company_forms', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_work_category_id')->nullable();

			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');
		});

		\DB::statement('CREATE UNIQUE INDEX vendor_performance_evaluation_company_forms_unique ON vendor_performance_evaluation_company_forms(vendor_performance_evaluation_id, vendor_work_category_id, company_id, evaluator_company_id) WHERE deleted_at IS NULL');

		$this->seed();

		DB::statement('ALTER TABLE vendor_performance_evaluation_company_forms ALTER COLUMN vendor_work_category_id SET NOT NULL');
	}

	protected function seed()
	{
		$seeder = new AddVendorWorkCategoryIdToVendorPerformanceEvaluationCompanyFormsTableSeeder;

		$seeder->run();
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
			$table->dropColumn('vendor_work_category_id');
		});
	}

}
