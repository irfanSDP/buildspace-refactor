<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsViewerAndIsRateEditorColumnToSiteManagementUserPermissionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('site_management_user_permissions', function (Blueprint $table)
		{
			$table->boolean('is_viewer')->default(false);
			$table->boolean('is_rate_editor')->default(false);
			
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
			$table->dropColumn('is_viewer');
			$table->dropColumn('is_rate_editor');

		});
	}

}
