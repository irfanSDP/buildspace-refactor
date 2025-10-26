<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrackRecordProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('track_record_projects', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title');
			$table->unsignedInteger('property_developer_id')->nullable();
			$table->string('property_developer_text')->nullable();
			$table->unsignedInteger('vendor_work_category_id');
			$table->unsignedInteger('company_id');
			$table->timestamps();

			$table->foreign('property_developer_id')->references('id')->on('property_developers');
			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories');
			$table->foreign('company_id')->references('id')->on('companies');

			$table->index('company_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('track_record_projects');
	}

}
