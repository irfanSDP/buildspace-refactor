<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\TenderFormVerifierLogs\FormLevelStatus;

class AddTechnicalEvaluationVerificationStatusColumnToTendersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders', function (Blueprint $table)
        {
            $table->smallInteger('technical_evaluation_verification_status', false, true)->index()->default(FormLevelStatus::IN_PROGRESS);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenders', function (Blueprint $table)
        {
            $table->dropColumn('technical_evaluation_verification_status');
        });
    }

}
