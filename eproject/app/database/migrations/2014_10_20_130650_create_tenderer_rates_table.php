<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTendererRatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tenderer_rates', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tenderer_id')->index();
			$table->string('rates');
			$table->string('tender_summary');
			$table->decimal('tender_amount', 19, 2)->default(0);
			$table->timestamps();

			$table->foreign('tenderer_id')->references('id')->on('tenderers');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tenderer_rates');
	}

}
