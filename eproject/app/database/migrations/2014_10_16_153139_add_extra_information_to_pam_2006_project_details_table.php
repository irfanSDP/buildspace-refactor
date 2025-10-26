<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddExtraInformationToPam2006ProjectDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('pam_2006_project_details', function (Blueprint $table)
		{
			$table->tinyInteger('period_of_architect_issue_interim_certificate', false, true)->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('pam_2006_project_details', function (Blueprint $table)
		{
			$table->dropColumn('period_of_architect_issue_interim_certificate');
		});
	}

}
