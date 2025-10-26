<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConfideSetupUsersTable extends Migration {

	/**
	 * Run the migrations.
	 */
	public function up()
	{
		// Creates the users table
		Schema::create('users', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id')->nullable()->index();
			$table->string('name');
			$table->string('contact_number');
			$table->string('username')->unique();
			$table->string('email')->unique();
			$table->string('password');
			$table->string('confirmation_code')->unique();
			$table->string('remember_token')->nullable();
			$table->boolean('confirmed')->default(false);
			$table->boolean('is_super_admin')->default(false);
			$table->boolean('is_admin')->default(false);
			$table->timestamps();

			$table->foreign('company_id')->references('id')->on('companies');
		});

		// Creates password reminders table
		Schema::create('password_reminders', function ($table)
		{
			$table->string('email')->index();
			$table->string('token')->index();

			$table->timestamp('created_at');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down()
	{
		Schema::drop('password_reminders');
		Schema::drop('users');
	}

}