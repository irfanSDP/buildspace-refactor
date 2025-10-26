<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSiteManagementDefectsColumnStatusIdToStatusId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_defects', function (Blueprint $table)
		{	
			$table->dropColumn('statusId');
			$table->unsignedInteger('status_id')->default(PCK\SiteManagement\SiteManagementDefect::STATUS_OPEN);
		});

	}

	/*
*	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('site_management_defects', function (Blueprint $table)
		{	
			$table->dropColumn('status_id');
			$table->unsignedInteger('statusId');
		});
	}

}
