<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractorsCommitmentStatusLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('contractors_commitment_status_logs', function(Blueprint $table)
		{
			$table->increments('id');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->unsignedInteger('loggable_id');
            $table->string('loggable_type');
            $table->unsignedInteger('status');

            $table->index(array('loggable_id', 'loggable_type', 'user_id'));

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
		Schema::drop('contractors_commitment_status_logs');
	}

}
