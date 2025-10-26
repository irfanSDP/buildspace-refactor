<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLoeFourthLevelMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('loe_fourth_level_messages', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('loss_or_and_expense_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('message');
			$table->decimal('grant_different_amount', 19, 2)->default(0);
			$table->smallInteger('decision', false, true)->nullable();
			$table->smallInteger('type', false, true)->index();
			$table->boolean('locked')->default(false);
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
		Schema::drop('loe_fourth_level_messages');
	}

}