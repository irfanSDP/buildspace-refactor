<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMobileSyncProjectLabourRatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('mobile_sync_project_labour_rates', function(Blueprint $table)
        {
            $table->unsignedInteger('project_labour_rate_id');
            $table->unsignedInteger('user_id');
            $table->string('device_id', 100);
            $table->boolean('synced')->default(false);

            $table->timestamps();

            $table->foreign('project_labour_rate_id')->references('id')->on('project_labour_rates');
            $table->foreign('user_id')->references('id')->on('users');

            $table->primary(['project_labour_rate_id', 'user_id', 'device_id']);
        });

        Schema::table('project_labour_rates', function(Blueprint $table)
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
        Schema::dropIfExists('mobile_sync_project_labour_rates');

        Schema::table('project_labour_rates', function(Blueprint $table)
        {
            $table->dropColumn('mobile_sync_uuid');
        });
	}

}
