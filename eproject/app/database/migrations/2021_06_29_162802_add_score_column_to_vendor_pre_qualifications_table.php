<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScoreColumnToVendorPreQualificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualifications', function(Blueprint $table)
		{
			$table->unsignedInteger('score')->nullable();
		});

		foreach(\PCK\VendorPreQualification\VendorPreQualification::all() as $vendorPreQualification)
		{
			$score = $vendorPreQualification->weightedNode->getScore();

			\DB::statement("UPDATE vendor_pre_qualifications SET score = ? WHERE id = ?", [$score, $vendorPreQualification->id]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_pre_qualifications', function(Blueprint $table)
		{
			$table->dropColumn('score');
		});
	}

}
