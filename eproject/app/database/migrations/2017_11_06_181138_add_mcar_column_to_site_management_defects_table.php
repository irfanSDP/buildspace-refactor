<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMcarColumnToSiteManagementDefectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_defects', function (Blueprint $table)
		{
			$table->integer('mcar_status')->default(\PCK\SiteManagement\SiteManagementMCAR::MCAR_NONE); 
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
			$table->dropColumn('mcar_status');
		});
		
	}

}
