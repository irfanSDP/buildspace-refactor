<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddContractGroupIdColumnToTenderInterviewInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_interview_information', function (Blueprint $table)
        {
            $table->unsignedInteger('contract_group_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tender_interview_information', function (Blueprint $table)
        {
            $table->dropColumn('contract_group_id');
        });
    }

}
