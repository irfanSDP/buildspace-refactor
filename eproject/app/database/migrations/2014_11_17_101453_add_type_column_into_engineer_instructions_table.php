<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTypeColumnIntoEngineerInstructionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('engineer_instructions', function (Blueprint $table)
		{
			$table->smallInteger('type', false, true)->index()->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('engineer_instructions', function (Blueprint $table)
		{
			$table->dropColumn('type');
		});
	}

}