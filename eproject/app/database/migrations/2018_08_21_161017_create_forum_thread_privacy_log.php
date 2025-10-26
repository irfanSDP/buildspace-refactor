<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumThreadPrivacyLog extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_thread_privacy_log', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('thread_id');
            $table->boolean('is_public');
            $table->unsignedInteger('created_by');
            $table->timestamps();

            $table->foreign('thread_id')->references('id')->on('forum_threads');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('forum_thread_privacy_log');
    }

}
