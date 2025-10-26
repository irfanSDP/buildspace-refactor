<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterVendorManagementGradeLevelsTableDropPriorityColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_management_grade_levels', function(Blueprint $table)
		{
			$table->dropColumn('priority');
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
			$table->integer('priority')->default(0);
		});
	}

}
