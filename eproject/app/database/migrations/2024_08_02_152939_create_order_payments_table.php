<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderPaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_payments', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->string('payment_gateway');
            $table->string('transaction_id')->nullable();
            $table->string('reference_id');
            $table->decimal('total', 24, 2)->unsigned();
            $table->string('status');
            $table->text('description')->nullable();
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
		Schema::drop('order_payments');
	}

}
