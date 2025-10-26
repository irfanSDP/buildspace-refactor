<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMobileSyncUploadsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('mobile_sync_uploads', function(Blueprint $table)
		{
            $table->unsignedInteger('upload_id');
            $table->unsignedInteger('user_id');
            $table->string('device_id', 100);
            $table->boolean('synced')->default(false);

            $table->timestamps();

            $table->foreign('upload_id')->references('id')->on('uploads');
            $table->foreign('user_id')->references('id')->on('users');

            $table->primary(['upload_id', 'user_id', 'device_id']);
		});

        Schema::table('uploads', function(Blueprint $table)
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
        Schema::drop('mobile_sync_uploads');

        Schema::table('uploads', function(Blueprint $table)
        {
            $table->dropColumn('mobile_sync_uuid');
        });
	}

}
