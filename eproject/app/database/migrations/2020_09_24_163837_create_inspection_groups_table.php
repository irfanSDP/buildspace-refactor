<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspection_groups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id');
			$table->string('name');
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

			$table->index('project_id');
			$table->unique(array('project_id', 'name'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspection_groups');
	}

}
