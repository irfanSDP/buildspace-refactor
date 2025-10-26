<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\ContractGroupCategory\ContractGroupCategory;

class AddTypeColumnToContractGroupCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('contract_group_categories', function(Blueprint $table)
		{
			$table->unsignedInteger('type')->default(ContractGroupCategory::TYPE_INTERNAL);
		});

		ContractGroupCategory::where('name', '=', ContractGroupCategory::CONTRACTOR_NAME)->update(array('type' => ContractGroupCategory::TYPE_EXTERNAL));
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
			$table->dropColumn('type');
		});
	}

}
