<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyTenderLotInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('company_tender_lot_information', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('company_id');
			$table->unsignedInteger('tender_lot_information_id');
			$table->boolean('added_by_gcd')->default(true);
			$table->text('remarks')->nullable();
			$table->timestamp('deleted_at')->nullable();
			$table->timestamps();

			$table->unique(array( 'company_id', 'tender_lot_information_id' ));

			$table->foreign('company_id')->references('id')->on('companies');
			$table->foreign('tender_lot_information_id')->references('id')->on('tender_lot_information');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('company_tender_lot_information');
	}

}
