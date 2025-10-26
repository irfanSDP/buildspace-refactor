<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DropColumnRegistrationIdFromCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("UPDATE companies SET reference_no = REGEXP_REPLACE(reference_no, '[^a-zA-Z0-9]', '', 'g');");

		Schema::table('companies', function (Blueprint $table)
        {
            $table->dropColumn('registration_id');
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
            $table->string('registration_id', 20)->default('');
        });

        $this->populateRegistrationIdColumn();
	}

	private function populateRegistrationIdColumn()
    {
        foreach(\PCK\Companies\Company::all() as $company)
        {
            $company->registration_id = \PCK\Companies\Company::generateRawRegistrationIdentifier($company->reference_no);
            $company->save();
        }
    }
}
