<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentProjectIdColumnToProjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function(Blueprint $table)
        {
            $table->unsignedInteger('parent_project_id')->nullable();

            $table->foreign('parent_project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function(Blueprint $table)
        {
            $table->dropColumn('parent_project_id');
        });
    }

}
