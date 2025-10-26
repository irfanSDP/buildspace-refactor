<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConversationReplyMessages extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('conversation_reply_messages', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('conversation_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->text('message');
			$table->smallInteger('status', false, true);
			$table->timestamps();

			$table->foreign('conversation_id')->references('id')->on('conversations');
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
		Schema::drop('conversation_reply_messages');
	}

}