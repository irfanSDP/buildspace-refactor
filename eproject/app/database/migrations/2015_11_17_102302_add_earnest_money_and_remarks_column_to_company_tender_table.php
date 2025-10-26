<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEarnestMoneyAndRemarksColumnToCompanyTenderTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('company_tender', function(Blueprint $table)
		{
			$table->boolean('earnest_money')->default(false);
            $table->string('remarks')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_tender', function(Blueprint $table)
		{
			$table->dropColumn('earnest_money');
			$table->dropColumn('remarks');
		});
	}

}
