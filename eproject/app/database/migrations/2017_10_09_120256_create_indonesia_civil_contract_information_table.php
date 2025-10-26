<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndonesiaCivilContractInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indonesia_civil_contract_information', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->date('commencement_date');
            $table->date('completion_date');
            $table->decimal('contract_sum', 19, 2);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('indonesia_civil_contract_information');
    }

}
