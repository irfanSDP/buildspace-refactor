<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumThreadsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_threads', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->string('title');
            $table->unsignedInteger('created_by');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('created_by')->references('id')->on('users');

            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('forum_threads');
    }

}
