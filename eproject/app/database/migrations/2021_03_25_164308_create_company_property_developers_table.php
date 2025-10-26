<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyPropertyDevelopersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_property_developers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('property_developer_id')->nullable();
			$table->string('name')->nullable();
			$table->timestamps();

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->foreign('property_developer_id')->references('id')->on('property_developers')->onDelete('cascade');

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
		Schema::drop('company_property_developers');
	}

}
