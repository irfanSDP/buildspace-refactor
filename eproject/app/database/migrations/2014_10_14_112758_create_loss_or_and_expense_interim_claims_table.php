<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLossOrAndExpenseInterimClaimsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loss_or_and_expense_interim_claims', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('loss_or_and_expense_id')->index();
			$table->unsignedInteger('interim_claim_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->timestamps();

			$table->foreign('loss_or_and_expense_id')->references('id')->on('loss_or_and_expenses');
			$table->foreign('interim_claim_id')->references('id')->on('interim_claims');
			$table->foreign('created_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('loss_or_and_expense_interim_claims');
	}

}