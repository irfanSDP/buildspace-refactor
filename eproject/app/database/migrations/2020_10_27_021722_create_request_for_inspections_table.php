<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestForInspectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_for_inspections', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id');
			$table->unsignedInteger('location_id');
			$table->unsignedInteger('inspection_list_category_id');
			$table->unsignedInteger('submitted_by');
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('inspection_list_category_id')->references('id')->on('inspection_list_categories')->onDelete('cascade');
			$table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');

			$table->unique('inspection_list_category_id');
			$table->index('project_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('request_for_inspections');
	}

}
