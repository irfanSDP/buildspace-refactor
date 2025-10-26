<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubmittedUserIdToSiteManagementDefectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_defects', function (Blueprint $table)
		{	
			$table->dropColumn('submitted_by');
		});

		Schema::table('site_management_defects', function (Blueprint $table)
		{	
			$table->integer('submitted_by'); 
			$table->foreign('submitted_by')->references('id')->on('users');
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
			$table->dropColumn('submitted_by');
		});

		Schema::table('site_management_defects', function (Blueprint $table)
		{
			$table->string('submitted_by');
		});
	}

}
