<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterColumnCompletionPeriodToTypeDecimal extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement('ALTER TABLE tender_rot_information ALTER COLUMN completion_period TYPE DECIMAL(19,2)');
		DB::statement('ALTER TABLE tender_rot_information ALTER COLUMN completion_period SET DEFAULT 0.0');

		DB::statement('ALTER TABLE tender_lot_information ALTER COLUMN completion_period TYPE DECIMAL(19,2)');
		DB::statement('ALTER TABLE tender_lot_information ALTER COLUMN completion_period SET DEFAULT 0.0');

		DB::statement('ALTER TABLE company_tender ALTER COLUMN completion_period TYPE DECIMAL(19,2)');
		DB::statement('ALTER TABLE company_tender ALTER COLUMN completion_period SET DEFAULT 0.0');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement('ALTER TABLE tender_rot_information ALTER COLUMN completion_period TYPE INTEGER');
		DB::statement('ALTER TABLE tender_rot_information ALTER COLUMN completion_period SET DEFAULT 0');

		DB::statement('ALTER TABLE tender_lot_information ALTER COLUMN completion_period TYPE INTEGER');
		DB::statement('ALTER TABLE tender_lot_information ALTER COLUMN completion_period SET DEFAULT 0');

		DB::statement('ALTER TABLE company_tender ALTER COLUMN completion_period TYPE INTEGER');
		DB::statement('ALTER TABLE company_tender ALTER COLUMN completion_period SET DEFAULT 0');
	}

}
