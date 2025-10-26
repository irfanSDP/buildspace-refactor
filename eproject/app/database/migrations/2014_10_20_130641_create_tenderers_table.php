<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTenderersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tenderers', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('company_id')->index();
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('company_id')->references('id')->on('companies');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tenderers');
	}

}