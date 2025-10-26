<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueConstraintsToCountryIdAndNameColumnsInStatesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('states', function(Blueprint $table)
        {
            $table->unique(array( 'country_id', 'name' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('states', function(Blueprint $table)
        {
            $table->dropUnique('states_country_id_name_unique');
        });
    }

}
