<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMobileSyncUuidColumnInSiteManagementDefectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('site_management_defects', function(Blueprint $table)
        {
            $table->string('mobile_sync_uuid', 60)->unique()->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('site_management_defects', function(Blueprint $table)
        {
            $table->dropColumn('mobile_sync_uuid');
        });
	}

}
