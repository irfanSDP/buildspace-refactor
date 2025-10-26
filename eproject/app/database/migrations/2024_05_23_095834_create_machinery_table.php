<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMachineryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('machinery', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->unsignedInteger('project_id')->index();
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
		});
	}
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('machinery');
	}

}
