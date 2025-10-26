<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCIDBCodeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('CIDB_code', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('code');
			$table->string('description');
			$table->unsignedInteger('parent_id')->nullable();
			$table->boolean('disabled')->default(false);
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
		Schema::drop('CIDB_code');
	}

}
