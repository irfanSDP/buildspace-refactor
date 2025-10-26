<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeightedNodeScoresTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('weighted_node_scores', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('node_id');
			$table->string('name', 255)->default('');
			$table->decimal('value', 5, 2)->default(0);
			$table->boolean('is_selected')->default(false);
			$table->timestamps();

			$table->foreign('node_id')->references('id')->on('weighted_nodes')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('weighted_node_scores');
	}

}
