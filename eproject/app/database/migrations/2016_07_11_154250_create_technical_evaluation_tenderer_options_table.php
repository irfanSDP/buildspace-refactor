<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalEvaluationTendererOptionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_evaluation_tenderer_options', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('option_id');
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('technical_evaluation_items')->onDelete('cascade');
            $table->foreign('option_id')->references('id')->on('technical_evaluation_items')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->index(array( 'company_id', 'item_id' ));
            $table->unique(array( 'company_id', 'item_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('technical_evaluation_tenderer_options');
    }

}
