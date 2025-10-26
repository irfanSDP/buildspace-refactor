<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateArchitectInstructionInterimClaimsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('architect_instruction_interim_claims', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('architect_instruction_id')->index();
			$table->unsignedInteger('interim_claim_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('subject');
			$table->text('letter_to_contractor');
			$table->timestamps();

			$table->foreign('architect_instruction_id')->references('id')->on('architect_instructions');
			$table->foreign('interim_claim_id')->references('id')->on('interim_claims');
			$table->foreign('created_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('architect_instruction_interim_claims');
	}

}