<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMobileSyncDefectCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('mobile_sync_defect_categories', function(Blueprint $table)
        {
            $table->unsignedInteger('defect_category_id');
            $table->unsignedInteger('user_id');
            $table->string('device_id', 100);
            $table->boolean('synced')->default(false);

            $table->timestamps();

            $table->foreign('defect_category_id')->references('id')->on('defect_categories');
            $table->foreign('user_id')->references('id')->on('users');

            $table->primary(['defect_category_id', 'user_id', 'device_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('mobile_sync_defect_categories');
	}

}
