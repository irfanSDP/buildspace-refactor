<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsEvaluationFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_evaluation_forms', function(Blueprint $table)
		{
            $table->increments('id');
            $table->integer('ds_evaluation_id')->unsigned();
            $table->integer('project_id')->unsigned()->nullable();
            $table->integer('weighted_node_id')->unsigned();
            $table->integer('score')->nullable();
            $table->integer('status_id')->unsigned();
            $table->integer('submitted_for_approval_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ds_evaluation_id')->references('id')->on('ds_evaluations')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('weighted_node_id')->references('id')->on('weighted_nodes')->onDelete('cascade');
            $table->foreign('submitted_for_approval_by')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_evaluation_forms');
	}

}
