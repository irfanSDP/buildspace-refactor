<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulePermissionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_permissions', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('module_identifier');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

            $table->unique(array( 'user_id', 'module_identifier' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('module_permissions');
    }

}
