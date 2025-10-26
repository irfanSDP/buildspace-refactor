<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumPostsReadLog extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_posts_read_log', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('post_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('post_id')->references('id')->on('forum_posts');
            $table->foreign('user_id')->references('id')->on('users');

            $table->index(array( 'user_id', 'post_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('forum_posts_read_log');
    }

}
