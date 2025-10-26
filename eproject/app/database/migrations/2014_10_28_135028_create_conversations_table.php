<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateConversationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('conversations', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->smallInteger('purpose_of_issued')->nullable()->index();
			$table->date('deadline_to_reply')->nullable()->default(null);
			$table->text('message');
			$table->smallInteger('status', false, true);
			$table->unsignedInteger('send_by_contract_group_id')->index();
			$table->timestamp('created_at');
			$table->timestamp('updated_at')->index();

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('created_by')->references('id')->on('users');
			$table->foreign('send_by_contract_group_id')->references('id')->on('contract_groups');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('conversations');
	}

}