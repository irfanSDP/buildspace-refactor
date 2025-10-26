<?php

use Illuminate\Database\Migrations\Migration;
use PCK\Companies\Company;

class RemoveNotNullConstraintForFaxNumberInCompaniesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE companies ALTER COLUMN fax_number DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Company::whereNull('fax_number')->update(array( 'fax_number' => '' ));

        DB::statement('ALTER TABLE companies ALTER COLUMN fax_number SET NOT NULL');
    }

}
