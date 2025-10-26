<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePaymentSettingsTable extends Migration
{
	public function up()
	{
		Schema::create('payment_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('account_number');
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();

			$table->index('created_by');
			$table->index('updated_by');
			
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});
	}

	public function down()
	{
		Schema::drop('payment_settings');
	}
}
