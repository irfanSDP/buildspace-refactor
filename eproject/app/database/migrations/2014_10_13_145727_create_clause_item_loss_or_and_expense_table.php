<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClauseItemLossOrAndExpenseTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('clause_item_loss_or_and_expense', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('clause_item_id')->index();
			$table->unsignedInteger('loss_or_and_expense_id')->index();
			$table->timestamps();

			$table->foreign('clause_item_id')->references('id')->on('clause_items')->onDelete('cascade');
			$table->foreign('loss_or_and_expense_id')->references('id')->on('loss_or_and_expenses')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('clause_item_loss_or_and_expense');
	}

}