<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RedefineCallingAndClosingDatesInOpenTenderPageInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('open_tender_page_information', function (Blueprint $table)
		{
			$table->dropColumn('calling_date_from')->nullable();
			$table->dropColumn('calling_date_to')->nullable();

			$table->dropColumn('closing_date')->nullable();
			$table->dropColumn('closing_time')->nullable();
		});

		Schema::table('open_tender_page_information', function (Blueprint $table)
		{
			$table->string('calling_date')->nullable();
			$table->string('closing_date')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('open_tender_page_information', function (Blueprint $table)
		{
			$table->dropColumn('calling_date')->nullable();
			$table->dropColumn('closing_date')->nullable();
		});

		Schema::table('open_tender_page_information', function (Blueprint $table)
		{
			$table->date('calling_date_from')->nullable();
			$table->date('calling_date_to')->nullable();

			$table->date('closing_date')->nullable();
			$table->string('closing_time')->nullable();
		});
	}

}
