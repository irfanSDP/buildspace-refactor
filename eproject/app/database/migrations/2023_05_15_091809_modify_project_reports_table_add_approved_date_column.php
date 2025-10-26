<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProjectReportsTableAddApprovedDateColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('project_reports', function(Blueprint $table)
        {
            $table->dateTime('approved_date')->nullable();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('project_reports', function(Blueprint $table)
        {
            $table->dropColumn('approved_date');
        });
	}
}
