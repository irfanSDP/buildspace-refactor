<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDiscussionTimeColumnToTenderInterviewInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tender_interview_information', function(Blueprint $table)
		{
            $table->timestamp('date_and_time')->default('now()');
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
            $table->dropColumn('date_and_time');
		});
	}

}
