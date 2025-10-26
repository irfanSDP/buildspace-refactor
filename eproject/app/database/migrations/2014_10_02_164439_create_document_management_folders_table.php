<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentManagementFoldersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('document_management_folders', function (Blueprint $table)
		{
			// These columns are needed for Baum's Nested Set implementation to work.
			// Column names may be changed, but they *must* all exist and be modified
			// in the model.
			// Take a look at the model scaffold comments for details.
			// We add indexes on parent_id, lft, rgt columns by default.
			$table->increments('id');
			$table->unsignedInteger('root_id')->nullable()->index();
			$table->unsignedInteger('parent_id')->nullable()->index();
			$table->unsignedInteger('lft')->nullable()->index();
			$table->unsignedInteger('rgt')->nullable()->index();
			$table->unsignedInteger('depth')->nullable();
			$table->unsignedInteger('priority');

			$table->string('name', 255);
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('contract_group_id')->nullable()->index();
			$table->unsignedInteger('folder_type')->nullable();

			$table->timestamps();

			$table->foreign('parent_id')->references('id')->on('document_management_folders')->onDelete('cascade');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('contract_group_id')->references('id')->on('contract_groups')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('document_management_folders');
	}

}
