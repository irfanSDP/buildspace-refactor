<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplierCreditFacilitiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('supplier_credit_facilities', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('supplier_name');
			$table->string('credit_facilities');
			$table->unsignedInteger('company_id');
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
		Schema::drop('supplier_credit_facilities');
	}

}
