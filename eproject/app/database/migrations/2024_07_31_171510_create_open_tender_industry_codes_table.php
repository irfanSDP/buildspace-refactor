<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenTenderIndustryCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('open_tender_industry_codes', function (Blueprint $table)
		{
			$table->increments('id');
			$table->unsignedInteger('tender_id');
			$table->unsignedInteger('created_by');

			$table->unsignedInteger('cidb_code_id')->nullable()->index();
			$table->unsignedInteger('cidb_grade_id')->nullable()->index();
			$table->unsignedInteger('vendor_category_id')->nullable()->index();
			$table->unsignedInteger('vendor_work_category_id')->nullable()->index();
			$table->timestamps();

			$table->foreign('tender_id')->references('id')->on('tenders');
			$table->foreign('created_by')->references('id')->on('users');

			$table->foreign('cidb_code_id')->references('id')->on('cidb_codes');
			$table->foreign('cidb_grade_id')->references('id')->on('cidb_grades');
			$table->foreign('vendor_category_id')->references('id')->on('vendor_categories');
			$table->foreign('vendor_work_category_id')->references('id')->on('vendor_work_categories');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('open_tender_industry_codes');
	}

}
