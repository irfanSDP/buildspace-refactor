<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDsCycleWeightedNodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ds_cycle_weighted_nodes', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('ds_cycle_id')->unsigned();
            $table->integer('weighted_node_id')->unsigned();
            $table->string('type');
            $table->timestamps();

            $table->foreign('ds_cycle_id')->references('id')->on('ds_cycles')->onDelete('cascade');
            $table->foreign('weighted_node_id')->references('id')->on('weighted_nodes')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ds_cycle_weighted_nodes');
	}

}
