<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \PCK\PaymentGateway\PaymentGatewaySetting;

class CreatePaymentGatewaySettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payment_gateway_settings', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('payment_gateway');
            $table->boolean('is_sandbox')->default(true);
            $table->boolean('is_active')->default(false);
            $table->string('merchant_id')->nullable();
            $table->string('key1')->nullable();
            $table->string('key2')->nullable();
            $table->text('button_image_url')->nullable();
			$table->timestamps();
		});

        $this->seed();
	}

    private function seed()
    {
        PaymentGatewaySetting::seed();
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payment_gateway_settings');
	}

}
