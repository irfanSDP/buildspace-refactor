<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class ModifyAiNumberColumnTypeInRequestForVariationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request_for_variations', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE request_for_variations ALTER COLUMN ai_number TYPE VARCHAR');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('request_for_variations', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE request_for_variations ALTER COLUMN ai_number TYPE INT USING (ai_number::INT)');
		});
	}

}
