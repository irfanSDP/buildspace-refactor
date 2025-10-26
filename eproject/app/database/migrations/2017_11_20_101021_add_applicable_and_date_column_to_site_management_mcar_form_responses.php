<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplicableAndDateColumnToSiteManagementMcarFormResponses extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_mcar_form_responses', function (Blueprint $table)
		{
			$table->dropColumn('corrective'); 

		});
		
		Schema::table('site_management_mcar_form_responses', function (Blueprint $table)
		{
			$table->integer('applicable')->default(\PCK\SiteManagement\SiteManagementMCARFormResponse::APPLICABLE_NONE); 
			$table->string('corrective')->nullable();
			$table->date('commitment_date')->nullable(); 
			$table->timestamp('verified_at')->nullable();
			$table->unsignedInteger('verifier_id')->nullable();

			$table->foreign('verifier_id')->references('id')->on('users');

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
			$table->dropColumn('applicable'); 
			$table->dropColumn('commitment_date'); 
			$table->dropColumn('verifier_id'); 
			$table->dropColumn('verified_at'); 
		});
	}

}
