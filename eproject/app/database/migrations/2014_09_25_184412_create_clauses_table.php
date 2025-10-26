<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClausesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clauses', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('contract_id');
			$table->string('name');
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
		Schema::drop('clauses');
	}

}