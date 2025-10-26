<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArchitectInstructionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('architect_instructions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('user_id')->index();
			$table->string('reference');
			$table->text('instruction');
			$table->date('deadline_to_comply')->nullable();
			$table->boolean('with_clauses')->default(false);
			$table->unsignedInteger('status');
			$table->tinyInteger('steps', false, true)->default(1);
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('architect_instructions');
	}

}