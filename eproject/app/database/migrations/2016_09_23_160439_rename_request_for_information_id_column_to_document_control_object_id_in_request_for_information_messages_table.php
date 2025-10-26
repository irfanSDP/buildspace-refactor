<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\DirectedTo\DirectedTo;
use PCK\RequestForInformation\RequestForInformationMessage;
use PCK\Verifier\Verifier;

class RenameRequestForInformationIdColumnToDocumentControlObjectIdInRequestForInformationMessagesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // We're not going to bother about existing data, so we just drop everything.

        // Remove relevant verifier records.
        DB::table('verifiers')->where('object_type', '=', get_class(new RequestForInformationMessage))->delete();

        // Detach messages from Contract groups.
        DB::table('directed_to')->where('object_type', '=', get_class(new RequestForInformationMessage))->delete();

        $createRequestForInformationMessagesTable = new CreateRequestForInformationMessagesTable;

        $createRequestForInformationMessagesTable->down();

        Schema::create('request_for_information_messages', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('document_control_object_id');
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
        Schema::drop('request_for_information_messages');

        $createRequestForInformationMessagesTable = new CreateRequestForInformationMessagesTable;

        $createRequestForInformationMessagesTable->up();
    }

}
