<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemarksColumnToContractorsCommitmentStatusLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('contractors_commitment_status_logs', function(Blueprint $table)
		{
			$table->text('remarks')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('contractors_commitment_status_logs', function(Blueprint $table)
		{
			$table->dropColumn('remarks');
		});
	}

}
