<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterRemarksColumnTypeInVerifiersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('verifiers', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE verifiers ALTER COLUMN remarks TYPE TEXT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('verifiers', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE verifiers ALTER COLUMN remarks TYPE VARCHAR(255)');
		});
	}

}
