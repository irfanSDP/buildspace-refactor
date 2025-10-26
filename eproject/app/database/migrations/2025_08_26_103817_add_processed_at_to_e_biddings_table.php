<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcessedAtToEBiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_biddings', 'processed_at')) {
            Schema::table('e_biddings', function (Blueprint $table) {
                $table->dateTime('processed_at')->nullable()->before('created_at');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (Schema::hasColumn('e_biddings', 'processed_at')) {
            Schema::table('e_biddings', function (Blueprint $table) {
                $table->dropColumn('processed_at');
            });
        }
	}

}
