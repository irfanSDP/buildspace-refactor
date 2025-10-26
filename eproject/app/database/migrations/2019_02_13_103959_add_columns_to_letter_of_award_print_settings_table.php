<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddColumnsToLetterOfAwardPrintSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('letter_of_award_print_settings', function(Blueprint $table)
		{
			$table->unsignedInteger('margin_top')->default(15);
			$table->unsignedInteger('margin_bottom')->default(20);
			$table->unsignedInteger('margin_left')->default(15);
			$table->unsignedInteger('margin_right')->default(15);
			$table->unsignedInteger('header_spacing')->default(5);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('letter_of_award_print_settings', function(Blueprint $table)
		{
			$table->dropColumn('margin_top');
			$table->dropColumn('margin_bottom');
			$table->dropColumn('margin_left');
			$table->dropColumn('margin_right');
			$table->dropColumn('header_spacing');
		});
	}

}
