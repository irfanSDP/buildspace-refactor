<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsEvaluationFormRemarksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_evaluation_form_remarks', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('ds_evaluation_form_id')->unsigned();
            $table->integer('ds_role_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('company_id')->unsigned()->nullable();
            $table->text('remarks')->nullable();
			$table->timestamps();

            $table->foreign('ds_evaluation_form_id')->references('id')->on('ds_evaluation_forms')->onDelete('cascade');
            $table->foreign('ds_role_id')->references('id')->on('ds_roles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_evaluation_form_remarks');
	}

}
