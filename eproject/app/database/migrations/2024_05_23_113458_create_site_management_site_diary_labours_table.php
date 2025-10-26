<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteManagementSiteDiaryLaboursTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_site_diary_labours', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('labour_id')->nullable()->index();
			$table->unsignedInteger('site_diary_id')->nullable();
			$table->integer('value')->nullable();
			$table->timestamps();

			$table->foreign('labour_id')->references('id')->on('labours');
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
		Schema::drop('site_management_site_diary_labours');
	}

}
