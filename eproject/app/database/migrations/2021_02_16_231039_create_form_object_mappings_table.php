<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFormObjectMappingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_object_mappings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('object_id');
			$table->string('object_class');
			$table->unsignedInteger('dynamic_form_id');
			$table->timestamps();

			$table->index('object_id');
			$table->index('dynamic_form_id');

			$table->foreign('dynamic_form_id')->references('id')->on('dynamic_forms')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('form_object_mappings');
	}

}
