<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateModuleUploadedFilesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('module_uploaded_files', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('upload_id');
			$table->unsignedInteger('uploadable_id');
			$table->string('uploadable_type');
			$table->timestamps();

			$table->foreign('upload_id')->references('id')->on('uploads');

			$table->unique(array( 'upload_id', 'uploadable_id', 'uploadable_type' ));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('module_uploaded_files');
	}

}