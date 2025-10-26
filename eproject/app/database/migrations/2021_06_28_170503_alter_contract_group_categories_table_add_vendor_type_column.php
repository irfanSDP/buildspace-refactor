<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\ContractGroupCategory\ContractGroupCategory;

class AlterContractGroupCategoriesTableAddVendorTypeColumn extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('contract_group_categories', function(Blueprint $table)
		{
			$table->integer('vendor_type')->default(ContractGroupCategory::VENDOR_TYPE_DEFAULT);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('contract_group_categories', function(Blueprint $table)
		{
			$table->dropColumn('vendor_type');
		});
	}

}
