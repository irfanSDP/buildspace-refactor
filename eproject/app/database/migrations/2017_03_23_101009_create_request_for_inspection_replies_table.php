<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestForInspectionRepliesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_for_inspection_replies', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('request_id');
            $table->unsignedInteger('inspection_id');
            $table->string('comments');
            $table->timestamp('ready_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('request_id')->references('id')->on('requests_for_inspection');
            $table->foreign('inspection_id')->references('id')->on('request_for_inspection_inspections');

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
        Schema::drop('request_for_inspection_replies');
    }

}
