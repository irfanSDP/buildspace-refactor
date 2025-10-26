<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormOfTenderDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_of_tender_details', function(Blueprint $table)
		{
			$table->increments('id');

            $table->unsignedInteger('tender_id');
            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('cascade');

            $table->boolean('editable')->default(true);

            $table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('form_of_tender_details');
	}

}
