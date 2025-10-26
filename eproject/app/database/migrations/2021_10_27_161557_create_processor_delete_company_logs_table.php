<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProcessorDeleteCompanyLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('processor_delete_company_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('reference_no');
			$table->unsignedInteger('contract_group_category_id');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->index('contract_group_category_id');
			$table->index('created_by');
			$table->index('updated_by');

			$table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('processor_delete_company_logs');
	}

}
