<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMobileSyncTradesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('mobile_sync_trades', function(Blueprint $table)
        {
            $table->unsignedInteger('trade_id');
            $table->unsignedInteger('user_id');
            $table->string('device_id', 100);
            $table->boolean('synced')->default(false);

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

            $table->primary(['trade_id', 'user_id', 'device_id']);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('mobile_sync_trades');
        Schema::dropIfExists('mobile_sync_project_labour_rate_trades');
	}

}
