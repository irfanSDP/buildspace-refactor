<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAeContractorConfirmDelaysTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ae_contractor_confirm_delays', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('additional_expense_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('message');
			$table->date('date_on_which_delay_is_over');
			$table->date('deadline_to_submit_final_claim');
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
		Schema::drop('ae_contractor_confirm_delays');
	}

}