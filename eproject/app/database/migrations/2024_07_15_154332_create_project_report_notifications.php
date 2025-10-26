<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectReportNotifications extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_report_notifications', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('project_id')->unsigned();
            $table->integer('project_report_type_mapping_id')->unsigned();
            $table->integer('category_column_id')->unsigned();
            $table->tinyInteger('notification_type')->unsigned()->default(1);
            $table->boolean('is_published')->default(false);
            $table->string('template_name')->nullable();
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
		Schema::drop('project_report_notifications');
	}

}
