<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirectedToTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directed_to', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('object_id');
            $table->string('object_type');
            $table->unsignedInteger('target_id');
            $table->string('target_type');
            $table->timestamps();

            $table->index(array( 'object_type', 'target_type' ));
            $table->unique(array( 'object_id', 'object_type', 'target_id', 'target_type' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('directed_to');
    }

}
