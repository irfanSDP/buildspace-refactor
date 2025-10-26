<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenTenderInformationPageTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_tender_page_information', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id');
			$table->unsignedInteger('created_by');

			$table->string('open_tender_type')->nullable();
			$table->string('open_tender_number')->nullable();
			$table->string('open_tender_price')->nullable();

			$table->date('open_tender_date_from')->nullable();
			$table->date('open_tender_date_to')->nullable();

			$table->date('calling_date_from')->nullable();
			$table->date('calling_date_to')->nullable();

			$table->date('closing_date')->nullable();
			$table->string('closing_time')->nullable();

			$table->text('deliver_address')->nullable();
			$table->string('briefing_time')->nullable();
			$table->text('briefing_address')->nullable();

			$table->boolean('special_permission')->default(false);
			$table->boolean('local_company_only')->default(false);

			$table->string('status')->nullable();

			$table->timestamps();

			$table->foreign('tender_id')->references('id')->on('tenders');
			$table->foreign('created_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('open_tender_page_information');
	}

}
