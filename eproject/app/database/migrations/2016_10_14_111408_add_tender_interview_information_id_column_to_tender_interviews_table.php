<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\TenderInterviews\TenderInterviewInformation;

class AddTenderInterviewInformationIdColumnToTenderInterviewsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_interviews', function (Blueprint $table)
        {
            $table->unsignedInteger('tender_interview_information_id')->default(0);

            $table->index('tender_interview_information_id');
        });

        foreach(\PCK\TenderInterviews\TenderInterview::all() as $companyInterview)
        {
            $interviewInfo = TenderInterviewInformation::where('tender_id', '=', $companyInterview->tender_id)->first();
            if(!$interviewInfo) continue;

            $companyInterview->tender_interview_information_id = $interviewInfo->id;
            $companyInterview->save();
        }

        Schema::table('tender_interviews', function (Blueprint $table)
        {
            $table->foreign('tender_interview_information_id')->references('id')->on('tender_interview_information');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tender_interviews', function (Blueprint $table)
        {
            $table->dropColumn('tender_interview_information_id');
        });
    }

}
