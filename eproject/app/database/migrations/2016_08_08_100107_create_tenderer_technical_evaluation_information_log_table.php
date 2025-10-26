<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTendererTechnicalEvaluationInformationLogTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenderer_technical_evaluation_information_log', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('information_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('information_id')->references('id')->on('tenderer_technical_evaluation_information');
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
        Schema::drop('tenderer_technical_evaluation_information_log');
    }

}
