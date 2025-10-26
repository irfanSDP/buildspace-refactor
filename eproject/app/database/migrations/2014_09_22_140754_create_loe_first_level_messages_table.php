<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLoeFirstLevelMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loe_first_level_messages', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('loss_or_and_expense_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('details');
			$table->boolean('decision')->nullable()->default(null);
			$table->smallInteger('type', false, true);
			$table->timestamps();

			$table->foreign('loss_or_and_expense_id')->references('id')->on('loss_or_and_expenses');
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
		Schema::drop('loe_first_level_messages');
	}

}