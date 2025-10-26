<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVendorWorkCategoryIdColumnToAccountCodeSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('account_code_settings', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_work_category_id')->nullable();
			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('account_code_settings', function(Blueprint $table)
		{
			$table->dropColumn('vendor_work_category_id');
		});
	}

}
