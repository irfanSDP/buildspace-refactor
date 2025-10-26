<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateTenderDocumentFilesRolesReadonlyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('template_tender_document_files_roles_readonly', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('template_tender_document_file_id');
            $table->unsignedInteger('contract_group_id');
			$table->timestamps();

            $table->index(array( 'template_tender_document_file_id', 'contract_group_id' ));

            $table->foreign('template_tender_document_file_id')->references('id')->on('template_tender_document_files');
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
		Schema::drop('template_tender_document_files_roles_readonly');
	}

}
