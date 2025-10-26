<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemProjectTendersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_item_project_tenders', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('order_item_id')->unsigned();
            $table->integer('project_id')->unsigned();
            $table->integer('tender_id')->unsigned();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('order_item_project_tenders');
	}

}
