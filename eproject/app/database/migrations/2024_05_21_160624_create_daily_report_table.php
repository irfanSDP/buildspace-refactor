<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyReportTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('daily_report', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('instruction');
            $table->dateTime('instruction_date');
			$table->unsignedInteger('submitted_by')->nullable();
			$table->integer('status');
			$table->timestamps();

			$table->index('submitted_by');
			
			$table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('daily_report');
	}

}
