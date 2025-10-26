<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateObjectLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('object_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('object_id');
            $table->string('object_class');
			$table->integer('module_identifier')->nullable();
			$table->integer('action_identifier');
			$table->unsignedInteger('user_id');
			$table->timestamps();

			$table->index('user_id');
			$table->index(['object_id', 'object_class'], 'object_id_class_index');
			$table->index(['object_id', 'object_class', 'module_identifier'], 'object_id_class_module_index');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('object_logs');
	}

}
