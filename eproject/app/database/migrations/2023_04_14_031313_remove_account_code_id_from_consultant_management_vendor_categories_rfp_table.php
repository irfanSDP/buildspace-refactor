<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAccountCodeIdFromConsultantManagementVendorCategoriesRfpTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$migration = new AddAccountCodeIdColumnInConsultantManagementVendorCategoriesRfp;
		$migration->down();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$migration = new AddAccountCodeIdColumnInConsultantManagementVendorCategoriesRfp;
		$migration->up();
	}

}
