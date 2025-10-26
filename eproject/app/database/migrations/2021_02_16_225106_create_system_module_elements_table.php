<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSystemModuleElementsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('system_module_elements', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('element_definition_id');
			$table->string('label');
			$table->text('instructions')->nullable();
			$table->boolean('is_key_information')->default(false);
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->index('element_definition_id');
			$table->index('created_by');
			$table->index('updated_by');

			$table->foreign('element_definition_id')->references('id')->on('element_definitions')->onDelete('cascade');
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
		Schema::drop('system_module_elements');
	}

}
