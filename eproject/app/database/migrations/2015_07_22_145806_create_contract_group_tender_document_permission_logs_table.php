<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContractGroupTenderDocumentPermissionLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contract_group_tender_document_permission_logs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('assign_company_log_id');
			$table->unsignedInteger('contract_group_id');
			$table->timestamps();

			$table->foreign('assign_company_log_id', 'tender_doc_permission_log_assign_company_fk')->references('id')->on('assign_companies_logs');
			$table->foreign('contract_group_id')->references('id')->on('contract_groups');

			$table->unique(array( 'assign_company_log_id', 'contract_group_id' ), 'tender_doc_permission_log_assign_company_contract_grp_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('contract_group_tender_document_permission_logs');
	}

}