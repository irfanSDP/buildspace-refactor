<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteManagementSiteDiaryRejectedMaterialsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_site_diary_rejected_materials', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('rejected_material_id')->nullable()->index();
			$table->unsignedInteger('site_diary_id')->nullable();
			$table->timestamps();

			$table->foreign('rejected_material_id')->references('id')->on('rejected_materials');
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
		Schema::drop('site_management_site_diary_rejected_materials');
	}

}
