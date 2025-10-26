<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemVendorRegPaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('order_item_vendor_reg_payments', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('order_item_id')->unsigned();
            $table->integer('vendor_registration_payment_id')->unsigned();
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
		Schema::drop('order_item_vendor_reg_payments');
	}

}
