<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentGatewayResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment_gateway_results', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('payment_gateway');
            $table->string('transaction_id')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('status');
            $table->string('info')->nullable();
            $table->boolean('verified')->default(false);
            $table->boolean('is_ipn')->default(false);
            $table->text('data')->nullable();
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
		Schema::drop('payment_gateway_results');
	}

}
