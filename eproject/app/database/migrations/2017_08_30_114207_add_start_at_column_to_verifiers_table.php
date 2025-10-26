<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStartAtColumnToVerifiersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('verifiers', function(Blueprint $table)
        {
            $table->timestamp('start_at')->nullable();
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
            $table->dropColumn('start_at');
        });
    }

}
