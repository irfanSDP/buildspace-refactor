<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSecondsTimerToEbiddingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (! Schema::hasColumn('e_biddings', 'duration_seconds'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->integer('duration_seconds')->default(0)->after('duration_minutes');
            });
        }

        if (! Schema::hasColumn('e_biddings', 'start_overtime_seconds'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->integer('start_overtime_seconds')->default(0)->after('start_overtime');
            });
        }

        if (! Schema::hasColumn('e_biddings', 'overtime_seconds'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->integer('overtime_seconds')->default(0)->after('overtime_period');
            });
        }

        if (! Schema::hasColumn('e_biddings', 'extended_seconds'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->integer('extended_seconds')->default(0)->after('duration_extended')->comment('Duration extended in seconds');
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
        if (Schema::hasColumn('e_biddings', 'duration_seconds'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->dropColumn('duration_seconds');
            });
        }

        if (Schema::hasColumn('e_biddings', 'start_overtime_seconds'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->dropColumn('start_overtime_seconds');
            });
        }

        if (Schema::hasColumn('e_biddings', 'overtime_seconds'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->dropColumn('overtime_seconds');
            });
        }

        if (Schema::hasColumn('e_biddings', 'extended_seconds'))
        {
            Schema::table('e_biddings', function(Blueprint $table)
            {
                $table->dropColumn('extended_seconds');
            });
        }
	}

}
