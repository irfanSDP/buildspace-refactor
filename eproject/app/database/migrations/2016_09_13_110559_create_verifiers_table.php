<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerifiersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verifiers', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('verifier_id');
            $table->unsignedInteger('object_id');
            $table->string('object_type');
            $table->unsignedInteger('sequence_number');
            $table->boolean('approved')->nullable();
            $table->timestamps();

            $table->foreign('verifier_id')->references('id')->on('users');

            $table->index('object_type');
            $table->unique(array( 'object_id', 'object_type', 'verifier_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('verifiers');
    }

}
