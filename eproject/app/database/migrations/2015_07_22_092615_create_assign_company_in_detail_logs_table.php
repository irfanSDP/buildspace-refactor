<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAssignCompanyInDetailLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('assign_company_in_detail_logs', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('assign_company_log_id');
			$table->unsignedInteger('contract_group_id');
			$table->unsignedInteger('company_id');
			$table->timestamps();

			$table->foreign('assign_company_log_id')->references('id')->on('assign_companies_logs');
			$table->foreign('contract_group_id')->references('id')->on('contract_groups');
			$table->foreign('company_id')->references('id')->on('companies');

			$table->unique(array( 'assign_company_log_id', 'contract_group_id', 'company_id' ));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('assign_company_in_detail_logs');
	}

}