<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenTenderPersonInChargeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_tender_person_in_charges', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id');
			$table->unsignedInteger('created_by');

			$table->string('name')->nullable();
			$table->string('email')->nullable();
			$table->string('phone_number')->nullable();
			$table->string('department')->nullable();

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
		Schema::drop('open_tender_person_in_charges');
	}

}
