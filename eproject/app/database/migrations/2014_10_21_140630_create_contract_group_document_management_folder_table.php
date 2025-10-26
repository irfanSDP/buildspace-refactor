<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContractGroupDocumentManagementFolderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contract_group_document_management_folder', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('contract_group_id')->index();
			$table->unsignedInteger('document_management_folder_id')->index();
			$table->timestamps();

			$table->foreign('contract_group_id')->references('id')->on('contract_groups');
			$table->foreign('document_management_folder_id')->references('id')->on('document_management_folders');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('contract_group_document_management_folder');
	}

}