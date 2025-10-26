<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse;


class DropColumnsFromSiteDiaryTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_site_diary_general_form_responses', function (Blueprint $table)
		{
			$table->dropColumn('status');
		});

		Schema::table('site_management_site_diary_general_form_responses', function (Blueprint $table)
		{
			$table->unsignedInteger('status')->default(SiteManagementSiteDiaryGeneralFormResponse::STATUS_OPEN);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */

	public function down()
	{
		Schema::table('site_management_site_diary_general_form_responses', function (Blueprint $table)
		{
			$table->dropColumn('status');
		});

		Schema::table('site_management_site_diary_general_form_responses', function (Blueprint $table)
		{
			$table->unsignedInteger('status')->default(SiteManagementSiteDiaryGeneralFormResponse::STATUS_OPEN);
		});
	}

}
