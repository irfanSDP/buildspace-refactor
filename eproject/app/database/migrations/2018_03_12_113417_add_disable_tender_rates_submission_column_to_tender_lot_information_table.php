<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisableTenderRatesSubmissionColumnToTenderLotInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_lot_information', function(Blueprint $table)
        {
            $table->boolean('disable_tender_rates_submission')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tender_lot_information', function(Blueprint $table)
        {
            $table->dropColumn('disable_tender_rates_submission');
        });
    }

}
