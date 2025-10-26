<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterVendorPreQualificationsTableAddSoftDeleteColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_pre_qualifications', function(Blueprint $table)
		{
			$table->softDeletes();
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
			$table->dropSoftDeletes();
		});
	}

}
