<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderDocumentFilesRolesReadonlyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tender_document_files_roles_readonly', function(Blueprint $table)
		{
            $table->increments('id');
            $table->unsignedInteger('tender_document_file_id');
            $table->unsignedInteger('contract_group_id');
            $table->timestamps();

            $table->index(array( 'tender_document_file_id', 'contract_group_id' ));

            $table->foreign('tender_document_file_id')->references('id')->on('tender_document_files');
            $table->foreign('contract_group_id')->references('id')->on('contract_groups');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tender_document_files_roles_readonly');
	}

}
