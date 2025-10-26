<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyMobileSyncDefectCategoryTradesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::dropIfExists('mobile_sync_defect_category_trades');

        Schema::create('mobile_sync_defect_category_trades', function(Blueprint $table)
        {
            $table->unsignedInteger('defect_category_trade_id');
            $table->unsignedInteger('user_id');
            $table->string('device_id', 100);
            $table->boolean('synced')->default(false);

            $table->timestamps();

            $table->foreign('defect_category_trade_id')->references('id')->on('defect_category_pre_defined_location_code');
            $table->foreign('user_id')->references('id')->on('users');

            $table->primary(['defect_category_trade_id', 'user_id', 'device_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('mobile_sync_defect_category_trades');
	}

}
