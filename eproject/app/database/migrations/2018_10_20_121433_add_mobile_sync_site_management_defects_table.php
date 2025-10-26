<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMobileSyncSiteManagementDefectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('mobile_sync_site_management_defects', function(Blueprint $table)
		{
            $table->unsignedInteger('site_management_defect_id');
            $table->unsignedInteger('user_id');
            $table->string('device_id', 100);
            $table->boolean('synced')->default(false);

            $table->timestamps();

            $table->foreign('site_management_defect_id')->references('id')->on('site_management_defects');
            $table->foreign('user_id')->references('id')->on('users');

            $table->primary(['site_management_defect_id', 'user_id', 'device_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('mobile_sync_site_management_defects');
	}

}
