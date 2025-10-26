<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteManagementDefectBackchargeDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('site_management_defect_backcharge_details', function(Blueprint $table)
		{
			$table->increments('id');
			$table->decimal('machinery',19,2);
			$table->decimal('material',19,2);
			$table->decimal('labour',19,2);
			$table->decimal('total',19,2);
			$table->unsignedInteger('user_id')->index();
			$table->unsignedInteger('site_management_defect_id')->index();
			$table->timestamps();

			$table->foreign('site_management_defect_id')->references('id')->on('site_management_defects');
			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('site_management_defect_backcharge_details');
	}

}
