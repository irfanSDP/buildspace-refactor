<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInstructionsToContractorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('instructions_to_contractors', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('instruction');
            $table->dateTime('instruction_date');
			$table->unsignedInteger('submitted_by')->nullable();
			$table->integer('status');
			$table->timestamps();

			$table->index('submitted_by');
			
			$table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('instructions_to_contractors');
	}

}
