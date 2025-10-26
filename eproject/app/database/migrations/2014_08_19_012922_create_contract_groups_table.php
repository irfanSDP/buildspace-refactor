<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContractGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contract_groups', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('contract_id')->index();
			$table->unsignedInteger('group')->index();
			$table->timestamps();

			$table->foreign('contract_id')->references('id')->on('contracts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('contract_groups');
	}

}