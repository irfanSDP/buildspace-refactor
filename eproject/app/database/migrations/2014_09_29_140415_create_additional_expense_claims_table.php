<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdditionalExpenseClaimsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('additional_expense_claims', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('additional_expense_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('message');
			$table->decimal('final_claim_amount', 19, 2)->default(0);
			$table->timestamps();

			$table->foreign('additional_expense_id')->references('id')->on('additional_expenses');
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
		Schema::drop('additional_expense_claims');
	}

}