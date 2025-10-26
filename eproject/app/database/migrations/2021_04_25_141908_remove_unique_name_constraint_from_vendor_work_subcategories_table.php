<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUniqueNameConstraintFromVendorWorkSubcategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_work_subcategories', function(Blueprint $table)
		{
			$table->dropUnique('vendor_work_subcategories_name_unique');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_work_subcategories', function(Blueprint $table)
		{
			$table->unique('name', 'vendor_work_subcategories_name_unique');
		});
	}

}
