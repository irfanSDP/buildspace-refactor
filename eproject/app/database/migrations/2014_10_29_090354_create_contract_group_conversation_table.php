<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContractGroupConversationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contract_group_conversation', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('contract_group_id')->index();
			$table->unsignedInteger('conversation_id')->index();
			$table->timestamps();

			$table->foreign('contract_group_id')->references('id')->on('contract_groups')->onDelete('cascade');
			$table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('contract_group_conversation');
	}

}