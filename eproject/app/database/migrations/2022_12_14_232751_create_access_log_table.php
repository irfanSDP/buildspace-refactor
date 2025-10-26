<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccessLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('access_log', function(Blueprint $table)
		{
			$table->bigIncrements('id');
			$table->string('ip_address', 45)->nullable();
			$table->text('user_agent')->nullable();
			$table->unsignedInteger('user_id')->nullable();
			$table->string('http_method', 6);
			$table->text('url');
			$table->text('url_path');
			$table->timestamp('created_at');

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});

		\DB::statement('ALTER TABLE "access_log" ADD COLUMN params json;');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('access_log');
	}

}
