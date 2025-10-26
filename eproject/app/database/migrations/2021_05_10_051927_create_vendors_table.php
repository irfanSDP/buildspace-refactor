<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendors', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_work_category_id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('type');
			$table->unsignedInteger('vendor_evaluation_cycle_score_id')->nullable();
			$table->boolean('is_qualified')->default(true);
			$table->timestamp('watch_list_entry_date')->nullable();
			$table->timestamp('watch_list_release_date')->nullable();
			$table->timestamps();

			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->foreign('vendor_evaluation_cycle_score_id')->references('id')->on('vendor_evaluation_cycle_scores')->onDelete('cascade');

			$table->unique(array('vendor_work_category_id', 'company_id'), 'vendors_unique');
			$table->index('type');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendors');
	}
}
