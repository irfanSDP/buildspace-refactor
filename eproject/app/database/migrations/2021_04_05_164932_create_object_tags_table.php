<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObjectTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('object_tags', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tag_id');
			$table->unsignedInteger('object_id');
			$table->string('object_class');
			$table->timestamps();

			$table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

			$table->unique(['tag_id', 'object_id', 'object_class']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('object_tags');
	}

}
