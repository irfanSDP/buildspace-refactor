<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformation;

class AddCompletionPeriodMetricColumnToTenderRotInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tender_rot_information', function(Blueprint $table)
        {
            $table->unsignedInteger('completion_period_metric')->default(TenderRecommendationOfTendererInformation::COMPLETION_PERIOD_METRIC_TYPE_MONTHS);
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
            $table->dropColumn('completion_period_metric');
        });
    }

}
