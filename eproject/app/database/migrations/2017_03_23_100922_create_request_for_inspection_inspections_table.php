<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestForInspectionInspectionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_for_inspection_inspections', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->string('comments');
            $table->string('remarks');
            $table->timestamp('inspected_at');
            $table->integer('status');
            $table->unsignedInteger('sequence_number');
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('request_id')->references('id')->on('requests_for_inspection');

            $table->index('request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('request_for_inspection_inspections');
    }

}
