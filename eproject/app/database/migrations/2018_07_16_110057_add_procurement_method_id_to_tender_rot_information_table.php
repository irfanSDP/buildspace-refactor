<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcurementMethodIdToTenderRotInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_rot_information', function(Blueprint $table)
        {
            $table->unsignedInteger('procurement_method_id')->nullable();

            $table->foreign('procurement_method_id')->references('id')->on('procurement_methods');
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
            $table->dropColumn('procurement_method_id');
        });
    }

}
