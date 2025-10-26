<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyCidbCodeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_cidb_code', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedBiginteger('company_id');
            $table->unsignedBiginteger('cidb_code_id');

			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('cidb_code_id')->references('id')->on('cidb_codes')->onDelete('cascade');

			$table->unique(array('company_id', 'cidb_code_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_cidb_code');
	}

}
