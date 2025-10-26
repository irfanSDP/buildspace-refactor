<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RedefineEnableDisableColumnInAcknowledgementLetterTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('acknowledgement_letters', function(Blueprint $table)
        {
        	$table->dropColumn('enable');
        	$table->dropColumn('disable');
        	$table->boolean('enable_letter')->default(false);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('acknowledgement_letters', function(Blueprint $table)
        {
        	$table->boolean('enable')->default(true);
			$table->boolean('disable')->default(true);
        });
	}

}
