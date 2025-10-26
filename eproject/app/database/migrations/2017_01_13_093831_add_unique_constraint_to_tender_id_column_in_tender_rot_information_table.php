<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueConstraintToTenderIdColumnInTenderRotInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_rot_information', function(Blueprint $table)
        {
            $table->unique('tender_id');
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
            $table->dropUnique('tender_rot_information_tender_id_unique');
        });
    }

}
