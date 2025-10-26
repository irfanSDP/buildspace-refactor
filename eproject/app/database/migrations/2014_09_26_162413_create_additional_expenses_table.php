<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAdditionalExpensesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('additional_expenses', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('architect_instruction_id')->nullable()->index();
			$table->unsignedInteger('created_by')->index();
			$table->date('commencement_date_of_event');
			$table->string('subject');
			$table->text('detailed_elaborations');
			$table->decimal('initial_estimate_of_claim', 19, 2)->default(0);
			$table->decimal('amount_claimed', 19, 2)->default(0);
			$table->decimal('amount_granted', 19, 2)->default(0);
			$table->smallInteger('status', false, true);
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('architect_instruction_id')->references('id')->on('architect_instructions');
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
		Schema::drop('additional_expenses');
	}

}