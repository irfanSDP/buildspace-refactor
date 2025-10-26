<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConsultantRemarksColumnConsultantManagementRfpInterviewConsultantsTable extends Migration
{
    public function up()
    {
        Schema::table('consultant_management_rfp_interview_consultants', function(Blueprint $table)
        {
            $table->text('consultant_remarks')->nullable();
        });
    }

    public function down()
    {
        Schema::table('consultant_management_rfp_interview_consultants', function(Blueprint $table)
        {
            $table->dropColumn('consultant_remarks');
        });
    }
}
