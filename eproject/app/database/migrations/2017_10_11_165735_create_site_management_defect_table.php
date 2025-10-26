<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteManagementDefectTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_defects', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('bill_column_setting_id')->nullable();
			$table->unsignedInteger('unit')->nullable();
			$table->integer('project_structure_location_code_id');
			$table->integer('pre_defined_location_code_id');
			$table->integer('contractor_id')->nullable();
			$table->unsignedInteger('statusId');
			$table->integer('defect_category_id');
			$table->integer('defect_id')->nullable(); 
			$table->string('remark');
			$table->string('path_to_defect_photo')->nullable(); 
			$table->unsignedInteger('pic_user_id')->nullable();
			$table->string('submitted_by');
			$table->unsignedInteger('project_id')->index();
			$table->timestamps();
			
			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('defect_category_id')->references('id')->on('defect_categories');
			$table->foreign('defect_id')->references('id')->on('defects'); 
			$table->foreign('contractor_id')->references('id')->on('companies');
			$table->foreign('pic_user_id')->references('id')->on('users');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('site_management_defects');
	}

}
