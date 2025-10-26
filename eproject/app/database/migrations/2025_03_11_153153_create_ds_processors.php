<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsProcessors extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_processors', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('ds_evaluation_id')->unsigned();
            $table->integer('user_id')->unsigned();
			$table->timestamps();

            $table->foreign('ds_evaluation_id')->references('id')->on('ds_evaluations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(array('ds_evaluation_id', 'user_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasTable('ds_processors')) {
            // Drop the table
            Schema::drop('ds_processors');
        }
	}

}
