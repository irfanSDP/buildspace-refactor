<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\Tenders\OpenTenderPageInformation;

class AddVerifierColumnsToOpenTenderPageInformationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('open_tender_page_information', function (Blueprint $table)
		{
			$table->dropColumn('status')->nullable();
		});

		Schema::table('open_tender_page_information', function (Blueprint $table)
		{
			$table->unsignedInteger('open_tender_status')->default(OpenTenderPageInformation::OPEN_TENDER_STATUS_ACTIVE);
			$table->unsignedInteger('submitted_for_approval_by')->nullable();
			$table->unsignedInteger('status')->default(OpenTenderPageInformation::STATUS_OPEN);
			$table->unsignedInteger('project_id')->nullable()->index();

			$table->foreign('project_id')->references('id')->on('projects');
			$table->foreign('submitted_for_approval_by')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('open_tender_page_information', function (Blueprint $table)
		{
			$table->dropColumn('submitted_for_approval_by');
			$table->dropColumn('project_id');
			$table->dropColumn('status');
			$table->dropColumn('open_tender_status');
		});

		Schema::table('open_tender_page_information', function (Blueprint $table)
		{
			$table->string('status')->nullable();
		});
	}

}
