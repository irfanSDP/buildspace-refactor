<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Clauses\Clause;

class AddTypeColumnToClausesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clauses', function(Blueprint $table)
        {
            $table->integer('type')->default(Clause::TYPE_MAIN);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clauses', function(Blueprint $table)
        {
            $table->dropColumn('type');
        });
    }

}
