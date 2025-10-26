<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTenderingInformationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tendering_informations', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('project_id')->index();
            $table->date('close_date');
            $table->time('close_time');
            $table->date('publish_date');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tendering_informations');
    }

}