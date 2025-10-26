<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderInterviewLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tender_interview_logs', function(Blueprint $table)
		{
			$table->increments('id');

            $table->unsignedInteger('user_id');
            $table->unsignedInteger('interview_id');
            $table->boolean('status');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('interview_id')->references('id')->on('tender_interviews')->onDelete('cascade');

            $table->index('interview_id');

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tender_interview_logs');
	}

}
