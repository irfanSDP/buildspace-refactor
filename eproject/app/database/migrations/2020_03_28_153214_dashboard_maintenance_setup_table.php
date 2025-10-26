<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DashboardMaintenanceSetupTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('dashboard_groups', function(Blueprint $table) {
            $table->integer('type')->unsigned()->unique();
            $table->timestamps();

            $table->primary('type');
        });

        Schema::create('dashboard_groups_users', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->unique();
            $table->integer('dashboard_group_type')->unsigned();

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('dashboard_group_type')->references('type')->on('dashboard_groups')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['user_id', 'dashboard_group_type']);
        });

        Schema::create('dashboard_groups_excluded_projects', function (Blueprint $table) {
            $table->integer('project_id')->unsigned();
            $table->integer('dashboard_group_type')->unsigned();

            $table->foreign('project_id')->references('id')->on('projects')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('dashboard_group_type')->references('type')->on('dashboard_groups')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['project_id', 'dashboard_group_type']);
        });

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dashboard_groups_users');
        Schema::drop('dashboard_groups_excluded_projects');
        Schema::drop('dashboard_groups');
    }

}
