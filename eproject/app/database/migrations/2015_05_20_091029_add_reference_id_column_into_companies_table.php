<?php

use PCK\Companies\Company;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferenceIdColumnIntoCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('companies', function (Blueprint $table)
		{
			$table->string('reference_id', Company::REFERENCE_ID_LENGTH)->nullable();

			$table->unique('reference_id');
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
			$table->dropColumn('reference_id');
		});
	}

}