<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalEvaluationResponseLogTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_evaluation_response_log', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('set_reference_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('set_reference_id')->references('id')->on('technical_evaluation_set_references');
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
        Schema::drop('technical_evaluation_response_log');
    }

}
