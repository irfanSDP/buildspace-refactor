<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameCidbGradesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::drop('CIDB_grades');
		Schema::create('cidb_grades', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('grade');
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
		Schema::drop('cidb_grades');
		Schema::create('CIDB_grades', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('grade');
			$table->unsignedInteger('parent_id')->nullable();
			$table->boolean('disabled')->default(false);
			$table->timestamps();

		});


	}

}
