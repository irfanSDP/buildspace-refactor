<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegistrationIdColumnToCompaniesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table)
        {
            $table->string('registration_id', 20)->default('');
        });

        $this->populateRegistrationIdColumn();

        Schema::table('companies', function (Blueprint $table)
        {
            $table->unique('registration_id');
        });

        // reference_no changes.
        DB::statement('ALTER TABLE companies ALTER COLUMN reference_no TYPE varchar(20);');
        DB::statement("UPDATE companies SET reference_no = id where reference_no IS NULL");
        DB::statement('ALTER TABLE companies ALTER COLUMN reference_no SET NOT NULL');

        // reference_id changes.
        DB::statement("UPDATE companies SET reference_id = id where reference_id IS NULL");
        DB::statement('ALTER TABLE companies ALTER COLUMN reference_id SET NOT NULL');
    }

    private function populateRegistrationIdColumn()
    {
        foreach(\PCK\Companies\Company::all() as $company)
        {
            $company->registration_id = \PCK\Companies\Company::generateRawRegistrationIdentifier($company->reference_no);
            $company->save();
        }
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
            $table->dropColumn('registration_id');
        });

        // reference_no changes.
        DB::statement('ALTER TABLE companies ALTER COLUMN reference_no TYPE varchar(60);');
        DB::statement('ALTER TABLE companies ALTER COLUMN reference_no DROP NOT NULL');

        // reference_id changes.
        DB::statement('ALTER TABLE companies ALTER COLUMN reference_id DROP NOT NULL');
    }

}
