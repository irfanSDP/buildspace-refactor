<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropEnabledColumnFromTenderInterviewInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tender_interview_information', function(Blueprint $table)
		{
			$table->dropColumn('enabled');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tender_interview_information', function(Blueprint $table)
		{
            $table->boolean('enabled')->default(true);
		});
	}

}
