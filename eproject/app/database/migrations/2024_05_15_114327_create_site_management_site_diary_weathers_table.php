<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteManagementSiteDiaryWeathersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_site_diary_weathers', function(Blueprint $table)
		{
			$table->increments('id');
			// weather form
			$table->string('weather_time_from')->nullable();
			$table->string('weather_time_to')->nullable();
			$table->unsignedInteger('site_diary_id')->nullable();
			$table->unsignedInteger('weather_id')->nullable()->index();
			$table->timestamps();

			$table->foreign('weather_id')->references('id')->on('weathers');
			$table->foreign('site_diary_id')->references('id')->on('site_management_site_diary_general_form_responses');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('site_management_site_diary_weathers');
	}

}
