<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFormColumnsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_columns', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('dynamic_form_id');
			$table->string('name');
			$table->integer('priority');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->index('dynamic_form_id');
			$table->index('created_by');
			$table->index('updated_by');
			
			$table->foreign('dynamic_form_id')->references('id')->on('dynamic_forms')->onDelete('cascade');
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
		Schema::drop('form_columns');
	}

}
