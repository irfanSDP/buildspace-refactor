<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinanceUserSubsidiariesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('finance_user_subsidiaries', function(Blueprint $table)
		{
			$table->unsignedInteger('user_id');
			$table->unsignedInteger('subsidiary_id');
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('subsidiary_id')->references('id')->on('subsidiaries')->onDelete('cascade');

			$table->unique(array( 'user_id', 'subsidiary_id' ));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('finance_user_subsidiaries');
	}

}
