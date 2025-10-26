<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRiskRegisterMessagesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_register_messages', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('document_control_object_id');
            $table->unsignedInteger('sequence_number');
            $table->unsignedInteger('composed_by');
            $table->timestamp('reply_deadline');
            $table->string('content');
            $table->unsignedInteger('type');
            $table->unsignedInteger('response_to')->nullable();
            $table->decimal('probability', 5, 2);
            $table->string('category');
            $table->string('trigger_event');
            $table->string('risk_response');
            $table->string('contingency_plan');
            $table->unsignedInteger('status');
            $table->unsignedInteger('impact');
            $table->unsignedInteger('detectability');
            $table->unsignedInteger('importance');
            $table->timestamps();

            $table->softDeletes();

            $table->foreign('document_control_object_id')->references('id')->on('document_control_objects');
            $table->foreign('composed_by')->references('id')->on('users');
            $table->foreign('response_to')->references('id')->on('request_for_information_messages');

            $table->index('document_control_object_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('risk_register_messages');
    }

}
