<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAeSecondLevelMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ae_second_level_messages', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('additional_expense_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('message');
			$table->date('requested_new_deadline');
			$table->date('grant_different_deadline')->nullable();
			$table->smallInteger('decision', false, true)->nullable();
			$table->smallInteger('type', false, true)->index();
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
		Schema::drop('ae_second_level_messages');
	}

}