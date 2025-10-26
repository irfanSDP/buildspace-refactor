<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteManagementSiteDiaryVisitorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_site_diary_visitors', function(Blueprint $table)
		{
			$table->increments('id');
			// visitor form
			$table->string('visitor_name')->nullable();
			$table->string('visitor_company_name')->nullable();
			$table->string('visitor_time_in')->nullable();
			$table->string('visitor_time_out')->nullable();
			$table->unsignedInteger('site_diary_id')->index();
			$table->timestamps();

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
		Schema::drop('site_management_site_diary_visitors');
	}

}
