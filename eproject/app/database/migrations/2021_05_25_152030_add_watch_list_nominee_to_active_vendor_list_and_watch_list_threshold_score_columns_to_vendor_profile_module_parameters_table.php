<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use PCK\ModuleParameters\VendorManagement\VendorProfileModuleParameter;

class AddWatchListNomineeToActiveVendorListAndWatchListThresholdScoreColumnsToVendorProfileModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_profile_module_parameters', function(Blueprint $table)
		{
			$table->unsignedInteger('watch_list_nomineee_to_active_vendor_list_threshold_score')->default(VendorProfileModuleParameter::WATCH_LIST_NOMINEEE_TO_ACTIVE_VENDOR_LIST_THRESHOLD_SCORE_DEFAULT_VALUE);
			$table->unsignedInteger('watch_list_nomineee_to_watch_list_threshold_score')->default(VendorProfileModuleParameter::WATCH_LIST_NOMINEEE_TO_WATCH_LIST_THRESHOLD_SCORE_DEFAULT_VALUE);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_profile_module_parameters', function(Blueprint $table)
		{
			$table->dropColumn('watch_list_nomineee_to_active_vendor_list_threshold_score');
			$table->dropColumn('watch_list_nomineee_to_watch_list_threshold_score');
		});
	}

}
