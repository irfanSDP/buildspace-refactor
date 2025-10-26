<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdditionalExpenseClauseItemTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('additional_expense_clause_item', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('additional_expense_id')->index();
			$table->unsignedInteger('clause_item_id')->index();
			$table->timestamps();

			$table->foreign('additional_expense_id')->references('id')->on('additional_expenses')->onDelete('cascade');
			$table->foreign('clause_item_id')->references('id')->on('clause_items')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('additional_expense_clause_item');
	}

}