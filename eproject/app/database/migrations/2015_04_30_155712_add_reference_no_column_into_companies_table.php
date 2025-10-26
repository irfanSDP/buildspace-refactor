<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddReferenceNoColumnIntoCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('companies', function (Blueprint $table)
		{
			$table->string('reference_no', 60)->nullable()->index();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('companies', function (Blueprint $table)
		{
			$table->dropColumn('reference_no');
		});
	}

}