<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyLabourReportLabourRatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('daily_labour_report_labour_rates', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('labour_type');
			$table->unsignedInteger('normal_working_hours')->default(0);
			$table->decimal('normal_rate')->default(0);
			$table->decimal('ot_rate')->default(0);
			
			$table->unsignedInteger('normal_workers_total')->default(0);
			$table->unsignedInteger('ot_workers_total')->default(0);
			$table->unsignedInteger('ot_hours_total')->default(0);
			$table->decimal('total_cost')->default(0);
			$table->unsignedInteger('daily_labour_report_id')->index();
			$table->timestamps();

			$table->foreign('daily_labour_report_id')->references('id')->on('daily_labour_reports');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('daily_labour_report_labour_rates');
	}

}
