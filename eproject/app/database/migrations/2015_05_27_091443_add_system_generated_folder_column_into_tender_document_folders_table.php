<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSystemGeneratedFolderColumnIntoTenderDocumentFoldersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tender_document_folders', function (Blueprint $table)
		{
			$table->boolean('system_generated_folder')
				->default(false)
				->index();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tender_document_folders', function (Blueprint $table)
		{
			$table->dropColumn('system_generated_folder');
		});
	}

}