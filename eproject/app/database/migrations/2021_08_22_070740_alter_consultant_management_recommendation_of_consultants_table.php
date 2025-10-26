<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterConsultantManagementRecommendationOfConsultantsTable extends Migration
{
    public function up()
    {
        Schema::table('consultant_management_recommendation_of_consultants', function(Blueprint $table)
        {
            DB::statement('ALTER TABLE consultant_management_recommendation_of_consultants ALTER COLUMN calling_rfp_proposed_date TYPE timestamp without time zone');
            DB::statement('ALTER TABLE consultant_management_recommendation_of_consultants ALTER COLUMN closing_rfp_proposed_date TYPE timestamp without time zone');
        });
    }

    public function down()
    {
        //no need to revert changes
    }
}
