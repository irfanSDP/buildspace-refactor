<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateElementsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('elements', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('parent_id')->nullable();
			$table->string('label')->nullable();
			$table->text('instructions')->nullable();
			$table->boolean('is_other_option')->default(false);
			$table->boolean('is_key_information')->default(false);
			$table->integer('priority');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->index('created_by');
			$table->index('updated_by');

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
		Schema::drop('elements');
	}

}
