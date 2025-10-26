<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInterimClaimsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('interim_claims', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('project_id')->index();
			$table->unsignedInteger('created_by')->index();
			$table->string('claim_no');
			$table->smallInteger('month', false, true);
			$table->smallInteger('year', false, true);
			$table->date('issue_certificate_deadline');
			$table->text('note');
			$table->decimal('amount_claimed', 19, 2)->default(0);
			$table->decimal('amount_granted', 19, 2)->default(0);
			$table->smallInteger('claim_counter', false, true)->nullable()->index();
			$table->smallInteger('status', false, true);
			$table->timestamps();

			$table->foreign('project_id')->references('id')->on('projects');
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
		Schema::drop('interim_claims');
	}

}