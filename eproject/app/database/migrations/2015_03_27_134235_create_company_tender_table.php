<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompanyTenderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_tender', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('tender_id');
			$table->string('rates')->nullable();
			$table->text('tender_amount')->nullable();
			$table->integer('completion_period')->default(0);
			$table->boolean('submitted')->default(false);
			$table->timestamps();

			$table->unique(array( 'company_id', 'tender_id' ));

			$table->foreign('company_id')->references('id')->on('companies');
			$table->foreign('tender_id')->references('id')->on('tenders');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_tender');
	}

}