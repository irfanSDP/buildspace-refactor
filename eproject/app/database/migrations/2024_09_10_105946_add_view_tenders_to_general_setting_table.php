<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddViewTendersToGeneralSettingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::drop('general_setting');

		Schema::create('general_settings', function (Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('view_own_created_subsidiary')->default(false);
			$table->boolean('view_tenders')->default(false);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('general_settings');

		Schema::create('general_setting', function (Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('view_own_created_subsidiary')->default(false);
			$table->timestamps();
		});
	}

}
