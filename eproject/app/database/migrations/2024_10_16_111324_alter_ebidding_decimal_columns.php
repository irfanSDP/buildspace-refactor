<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEbiddingDecimalColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        // This migration is to fix the issue where changes were made to the decimal columns of the e_biddings table migration file
        // but that migration file was already patched and run on some servers

        if (Schema::hasColumn('e_biddings', 'budget'))
        {
            DB::statement('ALTER TABLE e_biddings ALTER COLUMN budget TYPE DECIMAL(19, 2) USING budget::decimal(19, 2), ALTER COLUMN budget DROP NOT NULL');
        }

        if (Schema::hasColumn('e_biddings', 'decrement_value'))
        {
            DB::statement('ALTER TABLE e_biddings ALTER COLUMN decrement_value TYPE DECIMAL(19, 2) USING decrement_value::decimal(19, 2), ALTER COLUMN decrement_value DROP NOT NULL');
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        // Do nothing
	}

}
