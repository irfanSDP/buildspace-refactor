<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inspection_roles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id');
			$table->string('name');
			$table->boolean('can_request_inspection')->default(false);
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

			$table->unique(array('project_id', 'name'));
			$table->index('project_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspection_roles');
	}

}
