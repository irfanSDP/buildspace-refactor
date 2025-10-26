<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateElementAttributesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('element_attributes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('element_id');
			$table->string('element_class');
			$table->string('name');
			$table->string('value')->nullable();
			$table->timestamps();

			$table->index('element_id');

			$table->unique(['element_id', 'element_class', 'name']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('element_attributes');
	}

}
