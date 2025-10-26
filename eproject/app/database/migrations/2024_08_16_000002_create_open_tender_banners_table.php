<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenTenderBannersTable extends Migration {

	/**
	 * Run the migrations.
	 */
	public function up()
	{
		// Creates the news table
		Schema::create('open_tender_banners', function (Blueprint $table)
		{
			$table->increments('id');
			$table->string('image')->nullable();
			$table->integer('display_order')->nullable();
			$table->dateTime('start_time')->nullable();
			$table->dateTime('end_time')->nullable();
			$table->unsignedInteger('created_by')->index();
			$table->timestamps();

			$table->foreign('created_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down()
	{
		Schema::drop('open_tender_banners');
	}

}