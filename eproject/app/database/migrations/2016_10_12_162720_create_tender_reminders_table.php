<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenderRemindersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tender_reminders', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('tender_id');
            $table->unsignedInteger('updated_by');
            $table->text('message');
            $table->timestamps();

            $table->foreign('tender_id')->references('id')->on('tenders');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tender_reminders');
    }

}
