<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterVendorManagementGradeLevelsTableAddColumnDefinition extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_management_grade_levels', function(Blueprint $table)
		{
			$table->text('definition')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_management_grade_levels', function(Blueprint $table)
		{
			$table->dropColumn('definition');
		});
	}

}
