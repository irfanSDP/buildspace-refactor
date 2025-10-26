<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurgedVendorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('purged_vendors', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('reference_no', 20);
			$table->string('email');
			$table->string('telephone_number');
			$table->timestamp('purged_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('purged_vendors');
	}

}
