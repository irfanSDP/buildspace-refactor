<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectDocumentFiles extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_document_files', function (Blueprint $table)
		{
			$table->increments('id')->unique();
			$table->string('filename');
			$table->text('description')->nullable();
			$table->unsignedInteger('cabinet_file_id')->index();
			$table->unsignedInteger('project_document_folder_id')->index();
			$table->unsignedInteger('revision')->default(0);
			$table->unsignedInteger('parent_id')->nullable()->index(); // If this is a child file, it'll be referenced here.
			$table->timestamps();

			$table->foreign('cabinet_file_id')->references('id')->on('uploads')->onDelete('cascade');
			$table->foreign('project_document_folder_id')->references('id')->on('document_management_folders')->onDelete('cascade');
			$table->foreign('parent_id')->references('id')->on('project_document_files')->onDelete('cascade');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('project_document_files');
	}

}