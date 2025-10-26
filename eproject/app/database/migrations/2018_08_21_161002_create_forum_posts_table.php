<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumPostsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_posts', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('thread_id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('original_post_id')->nullable();
            $table->text('content');
            $table->unsignedInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('thread_id')->references('id')->on('forum_threads');
            $table->foreign('parent_id')->references('id')->on('forum_posts');
            $table->foreign('original_post_id')->references('id')->on('forum_posts');
            $table->foreign('created_by')->references('id')->on('users');

            $table->index(array( 'thread_id', 'parent_id', 'original_post_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('forum_posts');
    }

}
