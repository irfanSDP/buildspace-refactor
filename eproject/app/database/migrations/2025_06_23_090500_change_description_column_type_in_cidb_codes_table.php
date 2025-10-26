<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDescriptionColumnTypeInCidbCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cidb_codes', function (Blueprint $table)
		{
			DB::statement('ALTER TABLE cidb_codes ALTER COLUMN description TYPE TEXT');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cidb_codes', function (Blueprint $table)
		{
			DB::statement('ALTER TABLE cidb_codes ALTER COLUMN description TYPE VARCHAR(255)');
		});
	}

}
