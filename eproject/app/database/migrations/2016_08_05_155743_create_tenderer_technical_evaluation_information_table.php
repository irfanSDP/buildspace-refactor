<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTendererTechnicalEvaluationInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenderer_technical_evaluation_information', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('tender_id');
            $table->string('remarks');
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('tender_id')->references('id')->on('tenders');

            $table->unique(array( 'company_id', 'tender_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tenderer_technical_evaluation_information');
    }

}
