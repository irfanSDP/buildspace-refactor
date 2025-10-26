<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVendorManagementGradeLevelsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_management_grade_levels', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_management_grade_id');
			$table->string('description');
			$table->integer('score_upper_limit');
			$table->integer('priority');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->index('vendor_management_grade_id');

			$table->foreign('vendor_management_grade_id')->references('id')->on('vendor_management_grades')->onDelete('cascade');
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
		Schema::drop('vendor_management_grade_levels');
	}

}
