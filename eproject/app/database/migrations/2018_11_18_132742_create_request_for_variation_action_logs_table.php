<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRequestForVariationActionLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_for_variation_action_logs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('request_for_variation_id');
			$table->integer('user_id');
			$table->integer('permission_module_id');
			$table->integer('action_type');
			$table->integer('verifier')->nullable();
			$table->boolean('approved')->nullable();
			$table->string('remarks')->nullable();
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
		Schema::drop('request_for_variation_action_logs');
	}

}
