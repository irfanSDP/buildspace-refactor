<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorTypeChangeLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_type_change_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_id');
			$table->unsignedInteger('old_type');
			$table->unsignedInteger('new_type');
			$table->unsignedInteger('vendor_evaluation_cycle_score_id')->nullable();
			$table->timestamp('watch_list_entry_date')->nullable();
			$table->timestamp('watch_list_release_date')->nullable();
			$table->unsignedInteger('created_by')->nullable();
			$table->unsignedInteger('updated_by')->nullable();
			$table->timestamps();

			$table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
			$table->foreign('vendor_evaluation_cycle_score_id')->references('id')->on('vendor_evaluation_cycle_scores')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_type_change_logs');
	}

}
