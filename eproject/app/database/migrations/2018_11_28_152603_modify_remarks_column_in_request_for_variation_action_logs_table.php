<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class ModifyRemarksColumnInRequestForVariationActionLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('ALTER TABLE request_for_variation_action_logs ALTER COLUMN remarks TYPE TEXT');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('ALTER TABLE request_for_variation_action_logs ALTER COLUMN remarks TYPE VARCHAR');
	}

}
