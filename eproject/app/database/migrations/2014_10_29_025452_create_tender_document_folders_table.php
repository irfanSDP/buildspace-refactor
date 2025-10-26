<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTenderDocumentFoldersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tender_document_folders', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('root_id')->nullable()->index();
			$table->unsignedInteger('parent_id')->nullable()->index();
			$table->unsignedInteger('lft')->nullable()->index();
			$table->unsignedInteger('rgt')->nullable()->index();
			$table->unsignedInteger('depth')->nullable();
			$table->unsignedInteger('priority');

			$table->string('name', 255);
			$table->unsignedInteger('project_id')->index();

			$table->timestamps();

			$table->foreign('parent_id')->references('id')->on('tender_document_folders')->onDelete('cascade');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tender_document_folders');
	}

}
