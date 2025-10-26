<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRequestForVariationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('request_for_variations', function(Blueprint $table)
		{
			$table->increments('id'); 
			$table->integer('rfv_number');
			$table->integer('project_id');
			$table->integer('ai_number')->nullable();
			$table->string('description');
			$table->string('reasons_for_variation');
			$table->integer('category');
			$table->decimal('nett_omission_addition', 19, 2)->nullable();
			$table->integer('initiated_by');
			$table->string('time_implication')->nullable();
			$table->integer('status');
			$table->integer('permission_module_in_charge')->nullable();
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
		Schema::drop('request_for_variations');
	}

}
