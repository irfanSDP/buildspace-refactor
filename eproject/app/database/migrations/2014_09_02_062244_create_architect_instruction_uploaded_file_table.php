<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArchitectInstructionUploadedFileTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('architect_instruction_uploaded_file', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('architect_instruction_id')->index();
			$table->unsignedInteger('uploaded_file_id')->index();
			$table->timestamps();

			$table->foreign('architect_instruction_id')->references('id')->on('architect_instructions');
			$table->foreign('uploaded_file_id')->references('id')->on('uploaded_files');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('architect_instruction_uploaded_file');
	}

}