<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveNotNullConstraintFromTenderRecommendationOfTendererInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tender_rot_information', function(Blueprint $table)
		{
			DB::statement('ALTER TABLE tender_rot_information ALTER COLUMN consultant_estimates DROP NOT NULL');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tender_rot_information', function(Blueprint $table)
		{
			DB::statement('UPDATE tender_rot_information SET consultant_estimates = 0 where consultant_estimates IS NULL');
            DB::statement('ALTER TABLE tender_rot_information ALTER COLUMN consultant_estimates SET NOT NULL');
		});
	}

}
