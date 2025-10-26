<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalEvaluationItemTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_evaluation_items', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('name');
            $table->decimal('value', 5, 2)->default(0);
            $table->integer('type');
            $table->boolean('compulsory')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('technical_evaluation_items')->onDelete('cascade');
            $table->index(array( 'type', 'parent_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('technical_evaluation_items');
    }

}
