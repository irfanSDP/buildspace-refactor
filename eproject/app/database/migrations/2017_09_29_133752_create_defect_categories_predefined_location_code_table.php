<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefectCategoriesPredefinedLocationCodeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('defect_category_pre_defined_location_code', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('pre_defined_location_code_id')->unsigned();
			$table->integer('defect_category_id')->unsigned();
			$table->timestamps();
			$table->foreign('defect_category_id')->references('id')->on('defect_categories');
			$table->unique(['pre_defined_location_code_id','defect_category_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('defect_category_pre_defined_location_code');
	}

}
