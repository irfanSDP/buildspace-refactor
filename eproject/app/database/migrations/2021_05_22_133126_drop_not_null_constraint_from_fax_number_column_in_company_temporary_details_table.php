<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropNotNullConstraintFromFaxNumberColumnInCompanyTemporaryDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('ALTER TABLE company_temporary_details ALTER COLUMN fax_number DROP NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('company_temporary_details', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE company_temporary_details ALTER COLUMN fax_number SET NOT NULL');
		});
	}

}
