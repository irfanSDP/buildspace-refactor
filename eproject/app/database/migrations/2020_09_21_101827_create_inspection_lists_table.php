<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_lists', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('project_id')->nullable();
            $table->string('name');
            $table->unsignedInteger('priority');
            $table->timestamps();

            $table->index('project_id');

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::drop('inspection_lists');
    }
}
