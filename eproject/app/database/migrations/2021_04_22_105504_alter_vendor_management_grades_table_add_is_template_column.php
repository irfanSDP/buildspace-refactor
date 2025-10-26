<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterVendorManagementGradesTableAddIsTemplateColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_management_grades', function(Blueprint $table)
		{
			$table->boolean('is_template')->default(true);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_management_grades', function(Blueprint $table)
		{
			$table->dropColumn('is_template');
		});
	}

}
