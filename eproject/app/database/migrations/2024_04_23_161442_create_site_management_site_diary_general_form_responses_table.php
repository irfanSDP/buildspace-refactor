<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse;

class CreateSiteManagementSiteDiaryGeneralFormResponsesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_site_diary_general_form_responses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->date('general_date')->nullable();
			$table->string('general_time_in')->nullable();
			$table->string('general_time_out')->nullable();
			$table->string('general_day')->nullable();
			$table->decimal('general_physical_progress')->nullable();
			$table->decimal('general_plan_progress')->nullable();

			// weather form
			$table->string('weather_time_from')->nullable();
			$table->string('weather_time_to')->nullable();
			$table->unsignedInteger('weather_id')->nullable()->index();

			// labour form
			$table->integer('labour_project_manager')->nullable();
			$table->integer('labour_site_agent')->nullable();
			$table->integer('labour_supervisor')->nullable();

			// machinery form
			$table->integer('machinery_excavator')->nullable();
			$table->integer('machinery_backhoe')->nullable();
			$table->integer('machinery_crane')->nullable();

			// rejected material form
			$table->unsignedInteger('rejected_material_id')->nullable()->index();

			// visitor form
			$table->string('visitor_name')->nullable();
			$table->string('visitor_company_name')->nullable();
			$table->string('visitor_time_in')->nullable();
			$table->string('visitor_time_out')->nullable();

			// submitter information
			$table->unsignedInteger('submitted_by')->index();
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('submitted_for_approval_by')->nullable();
			$table->unsignedInteger('status')->default(SiteManagementSiteDiaryGeneralFormResponse::STATUS_OPEN);
			$table->timestamps();

			$table->foreign('submitted_by')->references('id')->on('users');
			$table->foreign('submitted_for_approval_by')->references('id')->on('users');
			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('weather_id')->references('id')->on('weathers');
			$table->foreign('rejected_material_id')->references('id')->on('rejected_materials');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('site_management_site_diary_general_form_responses');
	}

}
