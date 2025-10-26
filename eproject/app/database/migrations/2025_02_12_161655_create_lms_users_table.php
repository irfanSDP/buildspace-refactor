<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLmsUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('lms_users', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('user_id');
			$table->string('lms_course_id')->nullable();
			$table->string('lms_course_name')->nullable();
			$table->decimal('lms_course_score')->default(0);
			$table->boolean('lms_course_completed')->default(false);
			$table->string('lms_course_completed_at')->nullable();
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('lms_users');
	}
}
