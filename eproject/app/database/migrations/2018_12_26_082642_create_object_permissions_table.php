<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObjectPermissionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('object_permissions', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('object_id');
            $table->string('object_type');
            $table->boolean('is_editor')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');

            $table->index(array( 'object_type', 'object_id' ));

            $table->unique(array( 'object_type', 'object_id', 'user_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('object_permissions');
    }

}
