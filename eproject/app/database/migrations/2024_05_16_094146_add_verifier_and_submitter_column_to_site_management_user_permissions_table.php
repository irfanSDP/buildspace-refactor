<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVerifierAndSubmitterColumnToSiteManagementUserPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_user_permissions', function (Blueprint $table)
		{
			$table->boolean('is_verifier')->default(false);
			$table->boolean('is_submitter')->default(false);
			
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('site_management_user_permissions', function (Blueprint $table)
		{
			$table->dropColumn('is_verifier');
			$table->dropColumn('is_submitter');

		});
	}

}
