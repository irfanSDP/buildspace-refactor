<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnVendorWorkCategoryIdToVendorCategoryIdInAccountCodeSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$migration = new AddVendorWorkCategoryIdColumnToAccountCodeSettingsTable;
		$migration->down();

		Schema::table('account_code_settings', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_category_id')->nullable();
			$table->foreign('vendor_category_id')->references('id')->on('vendor_categories')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$migration = new AddVendorWorkCategoryIdColumnToAccountCodeSettingsTable;
		$migration->up();

		Schema::table('account_code_settings', function(Blueprint $table)
		{
			$table->dropColumn('vendor_category_id');
		});
	}

}
