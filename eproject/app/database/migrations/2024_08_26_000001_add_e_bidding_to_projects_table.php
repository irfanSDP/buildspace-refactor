<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEBiddingToProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('projects', function (Blueprint $table)
		{
			// Check if the column doesn't exist before adding it
			if (!Schema::hasColumn('projects', 'e_bidding')) {
                $table->boolean('e_bidding')->default(false);
            }
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('projects', function (Blueprint $table)
		{
			// Check if the column exists before trying to drop it
            if (Schema::hasColumn('projects', 'e_bidding')) {
                $table->dropColumn('e_bidding');
            }
		});
	}

}
