<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFormElementMappingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_element_mappings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('form_column_section_id');
			$table->unsignedInteger('element_id');
			$table->string('element_class');
			$table->integer('priority');
			$table->timestamps();

			$table->index('form_column_section_id');

			$table->foreign('form_column_section_id')->references('id')->on('form_column_sections');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('form_element_mappings');
	}

}
