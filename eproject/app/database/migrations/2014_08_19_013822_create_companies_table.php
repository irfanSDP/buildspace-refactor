<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('companies', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->text('address');
			$table->string('main_contact');
			$table->string('email');
			$table->string('telephone_number');
			$table->string('fax_number');
			$table->unsignedInteger('pam_2006_contract_group_id')->index();
			$table->timestamps();

			$table->foreign('pam_2006_contract_group_id')->references('id')->on('contract_groups');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('companies');
	}

}
