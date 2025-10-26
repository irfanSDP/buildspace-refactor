<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusIdToSiteManagementDefectBackchargeDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_defect_backcharge_details', function (Blueprint $table)
		{
			$table->integer('status_id')->default(\PCK\SiteManagement\SiteManagementDefectBackchargeDetail::STATUS_BACKCHARGE); 
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('site_management_defect_backcharge_details', function (Blueprint $table)
		{
			$table->dropColumn('status_id');
		});
	}

}
