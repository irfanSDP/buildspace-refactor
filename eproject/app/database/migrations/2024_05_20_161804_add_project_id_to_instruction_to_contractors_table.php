<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectIdToInstructionToContractorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('instructions_to_contractors', function (Blueprint $table)
		{
			$table->unsignedInteger('project_id')->nullable()->index();
			$table->foreign('project_id')->references('id')->on('projects');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('instructions_to_contractors', function (Blueprint $table)
		{
			$table->dropColumn('project_id');
		});
	}

}
