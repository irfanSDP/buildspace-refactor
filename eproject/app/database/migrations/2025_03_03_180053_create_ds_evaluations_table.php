<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsEvaluationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_evaluations', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('ds_cycle_id')->unsigned();
            $table->integer('company_id')->unsigned()->nullable();
            $table->integer('status_id')->unsigned();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ds_cycle_id')
                ->references('id')
                ->on('ds_cycles')
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('deleted_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_evaluations');
	}

}
