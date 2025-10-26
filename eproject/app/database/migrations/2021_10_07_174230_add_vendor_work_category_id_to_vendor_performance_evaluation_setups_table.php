<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorWorkCategoryIdToVendorPerformanceEvaluationSetupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_setups', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_work_category_id')->nullable();

			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');

			$table->dropUnique('vendor_performance_evaluation_setups_unique');

			$table->unique(['vendor_performance_evaluation_id', 'vendor_work_category_id', 'company_id'], 'vendor_performance_evaluation_setups_unique');
		});

		$this->seed();

		DB::statement('ALTER TABLE vendor_performance_evaluation_setups ALTER COLUMN vendor_work_category_id SET NOT NULL');
	}

	protected function seed()
	{
		$seeder = new AddVendorWorkCategoryIdToVendorPerformanceEvaluationSetupsTableSeeder;

		$seeder->run();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_setups', function(Blueprint $table)
		{
			$table->dropColumn('vendor_work_category_id');
			$table->unique(['vendor_performance_evaluation_id', 'company_id'], 'vendor_performance_evaluation_setups_unique');
		});
	}

}
