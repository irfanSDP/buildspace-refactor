<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClauseItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clause_items', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('clause_id');
			$table->char('no', 25);
			$table->text('description');
			$table->integer('priority');
			$table->timestamps();

			$table->foreign('clause_id')->references('id')->on('clauses');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('clause_items');
	}

}