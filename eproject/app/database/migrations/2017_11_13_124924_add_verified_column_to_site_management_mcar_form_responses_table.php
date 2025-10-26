<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVerifiedColumnToSiteManagementMcarFormResponsesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_mcar_form_responses', function (Blueprint $table)
		{
			$table->boolean('verified')->default(false); 
			$table->integer('satisfactory')->default(\PCK\SiteManagement\SiteManagementMCARFormResponse::VERIFIED_NONE); 
			$table->date('reinspection_date')->nullable(); 
			$table->unsignedInteger('site_management_mcar_id')->index();

			$table->foreign('site_management_mcar_id')->references('id')->on('site_management_mcar');
			
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('site_management_mcar_form_responses', function (Blueprint $table)
		{
			$table->dropColumn('verified');
			$table->dropColumn('satisfactory');
			$table->dropColumn('reinspection_date');
			$table->dropColumn('site_management_mcar_id');

		});
	}

}
