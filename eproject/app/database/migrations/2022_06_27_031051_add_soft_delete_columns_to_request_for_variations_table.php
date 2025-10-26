<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeleteColumnsToRequestForVariationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('request_for_variations', function(Blueprint $table)
		{
			$table->softDeletes();

			$table->unsignedInteger('deleted_by')->nullable();

			$table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('request_for_variations', function(Blueprint $table)
		{
			$table->dropSoftDeletes();

			$table->dropColumn('deleted_by');
		});
	}

}
