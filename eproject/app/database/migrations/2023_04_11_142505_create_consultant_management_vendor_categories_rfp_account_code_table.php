<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultantManagementVendorCategoriesRfpAccountCodeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('consultant_management_vendor_categories_rfp_account_code', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('vendor_category_rfp_id')->index('cm_rfp_account_code_vendor_category_rfp_id_idx');
			$table->unsignedInteger('account_code_id');
			$table->decimal('amount', 19, 2)->default(0);
			$table->unsignedInteger('created_by');
			$table->unsignedInteger('updated_by');
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('vendor_category_rfp_id')->references('id')->on('consultant_management_vendor_categories_rfp')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});

		DB::statement('CREATE UNIQUE INDEX cm_vendor_categories_rfp_account_code_unique ON consultant_management_vendor_categories_rfp_account_code(vendor_category_rfp_id, account_code_id) WHERE deleted_at IS NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('consultant_management_vendor_categories_rfp_account_code');
	}

}
