<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestsForInspectionTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests_for_inspection', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('reference_number');
            $table->string('inspection_reference');
            $table->string('subject');
            $table->string('description');
            $table->string('location');
            $table->string('works');
            $table->unsignedInteger('created_by');
            $table->integer('status');
            $table->timestamp('ready_date');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('created_by')->references('id')->on('users');

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
        Schema::drop('requests_for_inspection');
    }

}
