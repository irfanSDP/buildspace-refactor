<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestForInformationMessagesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Redefined in 2016_09_23_160439_rename_request_for_information_id_column_to_document_control_object_id_in_request_for_information_messages_table.php
        Schema::create('request_for_information_messages', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('request_for_information_id');
            $table->unsignedInteger('sequence_number');
            $table->unsignedInteger('composed_by');
            $table->timestamp('reply_deadline');
            $table->string('content');
            $table->unsignedInteger('type');
            $table->unsignedInteger('response_to')->nullable();
            $table->boolean('cost_impact')->default(false);
            $table->boolean('schedule_impact')->default(false);
            $table->timestamps();

            $table->softDeletes();

            $table->foreign('request_for_information_id')->references('id')->on('requests_for_information');
            $table->foreign('composed_by')->references('id')->on('users');
            $table->foreign('response_to')->references('id')->on('request_for_information_messages');

            $table->index('request_for_information_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('request_for_information_messages');
    }

}
