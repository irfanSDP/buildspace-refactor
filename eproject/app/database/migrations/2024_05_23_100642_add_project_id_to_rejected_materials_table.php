<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectIdToRejectedMaterialsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('rejected_materials', function (Blueprint $table)
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
		Schema::table('rejected_materials', function (Blueprint $table)
		{
			$table->dropColumn('project_id');
		});
	}

}
