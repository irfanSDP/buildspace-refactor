<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueConstraintToIso3ColumnInCountriesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('countries', function(Blueprint $table)
        {
            $table->unique('iso3');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('countries', function(Blueprint $table)
        {
            $table->dropUnique('countries_iso3_unique');
        });
    }

}
