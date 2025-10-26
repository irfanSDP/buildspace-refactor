<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenTenderNewsTable extends Migration {

	/**
	 * Run the migrations.
	 */
	public function up()
	{
		// Creates the news table
		Schema::create('open_tender_news', function (Blueprint $table)
		{
			$table->increments('id');
            $table->text('description')->nullable();
			$table->string('status')->nullable();
            $table->unsignedInteger('subsidiary_id')->nullable();
			$table->dateTime('start_time')->nullable();
			$table->dateTime('end_time')->nullable();
			$table->unsignedInteger('created_by')->index();
			$table->timestamps();

			$table->foreign('created_by')->references('id')->on('users');
            $table->foreign('subsidiary_id')->references('id')->on('subsidiaries');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down()
	{
		Schema::drop('open_tender_news');
	}

}