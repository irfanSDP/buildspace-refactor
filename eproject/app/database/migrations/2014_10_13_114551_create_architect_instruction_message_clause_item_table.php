<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArchitectInstructionMessageClauseItemTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('architect_instruction_message_clause_item', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('architect_instruction_message_id')->index();
			$table->unsignedInteger('clause_item_id')->index();
			$table->timestamps();

			$table->foreign('architect_instruction_message_id')->references('id')->on('architect_instruction_messages')->onDelete('cascade');
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
		Schema::drop('architect_instruction_message_clause_item');
	}

}