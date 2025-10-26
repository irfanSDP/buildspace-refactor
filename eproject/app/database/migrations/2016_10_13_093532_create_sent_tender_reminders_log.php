<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSentTenderRemindersLog extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sent_tender_reminders_log', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('sent_by');
            $table->unsignedInteger('tender_id');
            $table->timestamps();

            $table->foreign('sent_by')->references('id')->on('users');
            $table->foreign('tender_id')->references('id')->on('tenders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sent_tender_reminders_log');
    }

}
