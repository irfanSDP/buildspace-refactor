<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsEditableColumnToFormOfTenderClausesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_of_tender_clauses', function(Blueprint $table)
        {
            $table->boolean('is_editable')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_of_tender_clauses', function(Blueprint $table)
        {
            $table->dropColumn('is_editable');
        });
    }

}
