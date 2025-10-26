<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountRejectColumnToSiteManagementDefectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_defects', function (Blueprint $table)
		{
			$table->integer('count_reject')->default(0); 
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('site_management_defects', function (Blueprint $table)
		{
			$table->dropColumn('count_reject');
		});
	}

}
