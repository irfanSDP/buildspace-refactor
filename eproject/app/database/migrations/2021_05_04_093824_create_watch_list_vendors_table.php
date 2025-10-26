<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWatchListVendorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('watch_list_vendors', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_work_category_id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('vendor_evaluation_cycle_score_id');
			$table->timestamp('entry_date');
			$table->timestamp('release_date');
			$table->timestamps();

			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->foreign('vendor_evaluation_cycle_score_id')->references('id')->on('vendor_evaluation_cycle_scores')->onDelete('cascade');

			$table->unique(array('vendor_work_category_id', 'company_id', 'vendor_evaluation_cycle_score_id'), 'watch_list_vendors_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('watch_list_vendors');
	}

}
