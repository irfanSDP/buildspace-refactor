<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorPreQualificationVendorGroupGradesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('vendor_pre_qualification_vendor_group_grades', function(Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('contract_group_category_id');
			$table->unsignedInteger('vendor_management_grade_id')->nullable();
			$table->unsignedInteger('created_by')->nullable();
			$table->unsignedInteger('updated_by')->nullable();
			$table->timestamps();
			$table->softDeletes();

			$table->foreign('contract_group_category_id')->references('id')->on('contract_group_categories')->onDelete('cascade');
			$table->foreign('vendor_management_grade_id')->references('id')->on('vendor_management_grades')->onDelete('cascade');
			$table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
		});

		\DB::statement('CREATE UNIQUE INDEX vendor_pre_q_group_grades_contract_group_category_id_unique ON vendor_pre_qualification_vendor_group_grades(contract_group_category_id) WHERE deleted_at IS NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('vendor_pre_qualification_vendor_group_grades');
	}

}
