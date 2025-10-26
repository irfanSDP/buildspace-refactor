<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectContractGroupTenderDocumentPermissions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_contract_group_tender_document_permissions', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('contract_group_id')->nullable()->index();
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
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
		Schema::drop('project_contract_group_tender_document_permissions');
	}

}