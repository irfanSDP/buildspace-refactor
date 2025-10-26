<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddRequestRetenderAtAndRequestRetenderByAndRequestRetenderRemarksColumnsToTendersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tenders', function(Blueprint $table)
		{
			$table->dateTime('request_retender_at')->nullable();
			$table->unsignedInteger('request_retender_by')->nullable();
			$table->text('request_retender_remarks')->nullable();

			$table->index('request_retender_by');

			$table->foreign('request_retender_by')->references('id')->on('users');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tenders', function(Blueprint $table)
		{
			$table->dropColumn('request_retender_at');
			$table->dropColumn('request_retender_by');
			$table->dropColumn('request_retender_remarks');
		});
	}

}
