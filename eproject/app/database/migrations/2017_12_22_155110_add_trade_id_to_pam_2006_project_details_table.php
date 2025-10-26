<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTradeIdToPam2006ProjectDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('pam_2006_project_details', function (Blueprint $table)
		{
			$table->integer('pre_defined_location_code_id')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('pam_2006_project_details', function (Blueprint $table)
		{
			$table->dropColumn('pre_defined_location_code_id');
		});
	}

}
