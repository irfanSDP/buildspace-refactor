<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColummIdentifierIncreaseCharLimitTo255InSubsidiariesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('ALTER TABLE subsidiaries ALTER COLUMN identifier TYPE varchar(255);');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('ALTER TABLE subsidiaries ALTER COLUMN identifier TYPE varchar(10);');
	}

}
