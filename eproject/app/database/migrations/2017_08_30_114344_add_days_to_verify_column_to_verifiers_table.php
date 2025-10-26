<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDaysToVerifyColumnToVerifiersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('verifiers', function(Blueprint $table)
        {
            $table->unsignedInteger('days_to_verify')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verifiers', function(Blueprint $table)
        {
            $table->dropColumn('days_to_verify');
        });
    }

}
