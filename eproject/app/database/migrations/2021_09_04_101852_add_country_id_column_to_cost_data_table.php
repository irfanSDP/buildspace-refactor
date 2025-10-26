<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountryIdColumnToCostDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cost_data', function(Blueprint $table)
		{
			$table->unsignedInteger('country_id')->nullable();

			$table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cost_data', function(Blueprint $table)
		{
			$table->dropColumn('country_id');
		});
	}

}
