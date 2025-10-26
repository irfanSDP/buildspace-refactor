<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubsidiaryIdColumnToProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function(Blueprint $table)
		{
            $table->unsignedInteger('subsidiary_id')->nullable();

            $table->foreign('subsidiary_id')
                ->references('id')
                ->on('subsidiaries');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('projects', function(Blueprint $table)
		{
            $table->dropColumn('subsidiary_id');
		});
	}

}
