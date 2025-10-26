<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCodeColumnToVendorWorkSubcategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_work_subcategories', function(Blueprint $table)
		{
			$table->string('code', 50)->nullable();
		});

		\DB::statement('UPDATE vendor_work_subcategories SET code = name;');
		\DB::statement('ALTER TABLE vendor_work_subcategories ALTER COLUMN code SET NOT NULL');

		Schema::table('vendor_work_subcategories', function(Blueprint $table)
		{
			$table->unique('code');
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
			$table->dropColumn('code');
		});
	}

}
