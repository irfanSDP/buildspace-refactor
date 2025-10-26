<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTechnicalEvaluationSetReferencesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technical_evaluation_set_references', function (Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('set_id');
            $table->unsignedInteger('work_category_id')->nullable();
            $table->unsignedInteger('contract_limit_id')->nullable();
            $table->unsignedInteger('project_id')->nullable();
            $table->timestamps();

            $table->foreign('set_id')->references('id')->on('technical_evaluation_items')->onDelete('cascade');
            $table->foreign('work_category_id')->references('id')->on('work_categories')->onDelete('cascade');
            $table->foreign('contract_limit_id')->references('id')->on('contract_limits')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->unique(array( 'work_category_id', 'contract_limit_id' ));
            $table->unique(array( 'project_id' ));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('technical_evaluation_set_references');
    }

}
