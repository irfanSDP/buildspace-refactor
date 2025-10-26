<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyPersonnelTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_personnel', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('type');
			$table->string('name');
			$table->string('identification_number');
			$table->string('email_address')->nullable();
			$table->string('contact_number')->nullable();
			$table->string('years_of_experience')->nullable();
			$table->string('designation')->nullable();
			$table->string('amount_of_share')->nullable();
			$table->string('holding_percentage')->nullable();
			$table->timestamps();

			$table->foreign('company_id')->references('id')->on('companies');

			$table->index('company_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_personnel');
	}

}
