<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectRolesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_roles', function(Blueprint $table)
        {

            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('contract_group_id');
            $table->string('name', 100);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('contract_group_id')->references('id')->on('contract_groups');

            $table->index('project_id');
            $table->unique(array( 'project_id', 'contract_group_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('project_roles');
    }

}
