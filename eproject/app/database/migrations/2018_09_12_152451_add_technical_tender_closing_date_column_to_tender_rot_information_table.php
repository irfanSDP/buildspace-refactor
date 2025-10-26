<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTechnicalTenderClosingDateColumnToTenderRotInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_rot_information', function(Blueprint $table)
        {
            $table->dateTime('technical_tender_closing_date')->nullable();
        });

        DB::statement('UPDATE tender_rot_information SET technical_tender_closing_date = proposed_date_of_closing_tender;');
        DB::statement('ALTER TABLE tender_rot_information ALTER COLUMN technical_tender_closing_date SET NOT NULL');
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
            $table->dropColumn('technical_tender_closing_date');
        });
    }

}
