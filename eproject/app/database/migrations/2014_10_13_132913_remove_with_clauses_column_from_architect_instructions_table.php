<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveWithClausesColumnFromArchitectInstructionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('architect_instructions', function (Blueprint $table)
		{
			$table->dropColumn('with_clauses');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('architect_instructions', function (Blueprint $table)
		{
			$table->boolean('with_clauses')->default(false);
		});
	}

}