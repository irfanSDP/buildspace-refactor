<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArchitectInstructionThirdLevelMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ai_third_level_messages', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('architect_instruction_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('reason');
			$table->date('compliance_date')->nullable();
			$table->smallInteger('compliance_status', false, true)->nullable();
			$table->smallInteger('type', false, true);
			$table->timestamps();

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
		Schema::drop('ai_third_level_messages');
	}

}