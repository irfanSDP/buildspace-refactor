<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdditionalElementValuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('additional_element_values', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('element_value_id');
			$table->text('value');
			$table->timestamps();

			$table->index('element_value_id');
			$table->foreign('element_value_id')->references('id')->on('element_values')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('additional_element_values');
	}

}
