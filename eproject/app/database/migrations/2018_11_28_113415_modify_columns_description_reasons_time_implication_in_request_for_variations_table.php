<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class ModifyColumnsDescriptionReasonsTimeImplicationInRequestForVariationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('ALTER TABLE request_for_variations ALTER COLUMN description TYPE TEXT');
		DB::statement('ALTER TABLE request_for_variations ALTER COLUMN reasons_for_variation TYPE TEXT');
		DB::statement('ALTER TABLE request_for_variations ALTER COLUMN time_implication TYPE TEXT');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('ALTER TABLE request_for_variations ALTER COLUMN description TYPE VARCHAR');
		DB::statement('ALTER TABLE request_for_variations ALTER COLUMN reasons_for_variation TYPE VARCHAR');
		DB::statement('ALTER TABLE request_for_variations ALTER COLUMN time_implication TYPE VARCHAR');
	}

}
