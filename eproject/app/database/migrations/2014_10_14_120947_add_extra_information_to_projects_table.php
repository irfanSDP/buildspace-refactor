<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\Projects\Project;

class AddExtraInformationToProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function (Blueprint $table)
		{
			$table->unsignedInteger('status_id')->default(Project::STATUS_TYPE_DESIGN)->index();
			$table->unsignedInteger('country_id')->index()->nullable();
			$table->unsignedInteger('state_id')->index()->nullable();
			$table->string('employer_name')->nullable();
			$table->string('employer_address')->nullable();

			$table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
			$table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('projects', function (Blueprint $table)
		{
			$table->dropColumn('employer_address');
			$table->dropColumn('employer_name');
			$table->dropColumn('state_id');
			$table->dropColumn('country_id');
			$table->dropColumn('status_id');
		});
	}

}