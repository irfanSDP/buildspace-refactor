<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestsForInformationTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests_for_information', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('reference_number');
            $table->string('subject');
            $table->unsignedInteger('issuer_id');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('issuer_id')->references('id')->on('users');

            $table->index('project_id');
            $table->unique(array( 'project_id', 'reference_number' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('requests_for_information');
    }

}
