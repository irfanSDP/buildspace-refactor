<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArchitectInstructionEngineerInstructionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('architect_instruction_engineer_instruction', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('architect_instruction_id')->index();
			$table->unsignedInteger('engineer_instruction_id')->index();
			$table->timestamps();

			$table->foreign('architect_instruction_id')->references('id')->on('architect_instructions');
			$table->foreign('engineer_instruction_id')->references('id')->on('engineer_instructions');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('architect_instruction_engineer_instruction');
	}

}