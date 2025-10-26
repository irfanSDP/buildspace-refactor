<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('defects', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->unsignedInteger('defect_category_id')->index();
			$table->timestamps();
			$table->foreign('defect_category_id')->references('id')->on('defect_categories');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('defects');
	}

}
