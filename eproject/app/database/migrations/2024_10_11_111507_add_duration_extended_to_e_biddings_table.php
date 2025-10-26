<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDurationExtendedToEBiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_biddings', 'duration_extended'))
        {
            Schema::table('e_biddings', function (Blueprint $table) {
                $table->integer('duration_extended')->default(0)->after('duration_minutes');
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
        if (Schema::hasColumn('e_biddings', 'duration_extended'))
        {
            Schema::table('e_biddings', function (Blueprint $table) {
                $table->dropColumn('duration_extended');
            });
        }
	}

}
