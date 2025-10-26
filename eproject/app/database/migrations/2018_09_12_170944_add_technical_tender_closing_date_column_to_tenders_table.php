<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTechnicalTenderClosingDateColumnToTendersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders', function(Blueprint $table)
        {
            $table->dateTime('technical_tender_closing_date')->nullable();
        });

        DB::statement('UPDATE tenders SET technical_tender_closing_date = tender_closing_date;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenders', function(Blueprint $table)
        {
            $table->dropColumn('technical_tender_closing_date');
        });
    }

}
