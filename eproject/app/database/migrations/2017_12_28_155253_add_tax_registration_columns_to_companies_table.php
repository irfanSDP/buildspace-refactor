<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxRegistrationColumnsToCompaniesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function(Blueprint $table)
        {
            $table->string('tax_registration_no', 20)->nullable();
            $table->string('tax_registration_id', 20)->nullable();
            $table->unique('tax_registration_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function(Blueprint $table)
        {
            $table->dropColumn('tax_registration_no');
            $table->dropColumn('tax_registration_id');
        });
    }

}
