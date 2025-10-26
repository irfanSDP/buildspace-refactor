<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormOfTenderLogsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_of_tender_logs', function (Blueprint $table)
        {
            $table->increments('id');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedInteger('tender_id');
            $table->foreign('tender_id')->references('id')->on('tenders')->onDelete('cascade');

            $table->boolean('is_template')->default(false);

            $table->index('tender_id');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('form_of_tender_logs');
    }

}
