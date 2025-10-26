<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpressionOfInterestTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('expression_of_interest_tokens', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('tenderstageable_id');
			$table->string('tenderstageable_type', 255);
			$table->integer('user_id');
			$table->integer('company_id');
			$table->string('token', 255);
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
		Schema::drop('expression_of_interest_tokens');
	}

}
