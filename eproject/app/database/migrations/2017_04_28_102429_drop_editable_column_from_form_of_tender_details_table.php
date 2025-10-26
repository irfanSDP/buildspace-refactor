<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropEditableColumnFromFormOfTenderDetailsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_of_tender_details', function(Blueprint $table)
        {
            $table->dropColumn('editable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_of_tender_details', function(Blueprint $table)
        {
            $table->boolean('editable')->default(true);
        });
    }

}
