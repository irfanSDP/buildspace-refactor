<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalEvaluationVerifierLogs extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_evaluation_verifier_logs', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('tender_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('type');
            $table->timestamps();

            $table->index(array( 'tender_id', 'user_id' ));

            $table->foreign('tender_id')->references('id')->on('tenders');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('technical_evaluation_verifier_logs');
    }

}
