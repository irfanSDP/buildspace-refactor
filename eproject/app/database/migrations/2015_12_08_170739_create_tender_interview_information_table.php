<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderInterviewInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tender_interview_information', function(Blueprint $table)
		{
			$table->increments('id');
            $table->unsignedInteger('tender_id')->unique();
            $table->boolean('enabled')->default(true);
			$table->timestamps();

            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('cascade');
            $table->index('tender_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tender_interview_information');
	}

}
