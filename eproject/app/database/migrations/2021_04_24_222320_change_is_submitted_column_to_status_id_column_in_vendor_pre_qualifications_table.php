<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIsSubmittedColumnToStatusIdColumnInVendorPreQualificationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualifications', function(Blueprint $table)
		{
			$table->unsignedInteger('status_id')->default(\PCK\VendorPreQualification\VendorPreQualification::STATUS_DRAFT);
			$table->dropColumn('is_submitted');
		});
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
			$table->boolean('is_submitted')->default(false);
			$table->dropColumn('status_id');
		});
	}

}
