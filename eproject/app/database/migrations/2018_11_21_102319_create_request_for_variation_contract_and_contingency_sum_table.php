<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRequestForVariationContractAndContingencySumTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_for_variation_contract_and_contingency_sum', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('project_id');
			$table->decimal('original_contract_sum', 19, 2);
			$table->decimal('contingency_sum', 19, 2);
			$table->integer('user_id');
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
		Schema::drop('request_for_variation_contract_and_contingency_sum');
	}

}
