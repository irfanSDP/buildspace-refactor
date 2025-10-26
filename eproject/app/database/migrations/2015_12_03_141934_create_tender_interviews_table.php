<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderInterviewsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tender_interviews', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id');
			$table->unsignedInteger('company_id');
			$table->string('venue');
			$table->timestamp('date_and_time');
            $table->integer('status')->default(\PCK\TenderInterviews\TenderInterview::STATUS_DEFAULT);
            $table->string('key')
                ->nullable()
                ->unique()
                ->default(null);
			$table->timestamps();

            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

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
		Schema::drop('tender_interviews');
	}

}
