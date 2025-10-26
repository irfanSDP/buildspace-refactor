<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderSubsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_subs', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->string('reference_id');
            $table->integer('company_id')->unsigned()->nullable()->comment('Seller');
            $table->decimal('total', 24, 2)->unsigned();
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
		Schema::drop('order_subs');
	}

}
