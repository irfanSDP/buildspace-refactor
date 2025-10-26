<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisableTenderRatesSubmissionColumnToTenderRotInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_rot_information', function(Blueprint $table)
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
        Schema::table('tender_rot_information', function(Blueprint $table)
        {
            $table->dropColumn('disable_tender_rates_submission');
        });
    }

}
