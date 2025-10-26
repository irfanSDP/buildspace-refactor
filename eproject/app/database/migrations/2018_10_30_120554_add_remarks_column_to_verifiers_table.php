<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddRemarksColumnToVerifiersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('verifiers', function(Blueprint $table)
		{
			$table->string('remarks')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('verifiers', function(Blueprint $table)
		{
			$table->dropColumn('remarks');
		});
	}

}
