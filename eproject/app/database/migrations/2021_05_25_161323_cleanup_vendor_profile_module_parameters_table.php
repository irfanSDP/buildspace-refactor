<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanupVendorProfileModuleParametersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_profile_module_parameters', function(Blueprint $table)
		{
			$table->renameColumn('grade_period_of_expired_vendor_before_moving_to_dvl_value', 'grace_period_of_expired_vendor_before_moving_to_dvl_value');
			$table->renameColumn('grade_period_of_expired_vendor_before_moving_to_dvl_unit', 'grace_period_of_expired_vendor_before_moving_to_dvl_unit');
			$table->renameColumn('vendor_retrain_period_in_wl_value', 'vendor_retain_period_in_wl_value');
			$table->renameColumn('vendor_retrain_period_in_wl_unit', 'vendor_retain_period_in_wl_unit');
			$table->dropColumn('minimum_vpe_score_to_exit_wl_value');
			$table->dropColumn('minimum_vpe_score_to_exit_wl_unit');
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
			$table->renameColumn('grace_period_of_expired_vendor_before_moving_to_dvl_value', 'grade_period_of_expired_vendor_before_moving_to_dvl_value');
			$table->renameColumn('grace_period_of_expired_vendor_before_moving_to_dvl_unit', 'grade_period_of_expired_vendor_before_moving_to_dvl_unit');
			$table->renameColumn('vendor_retain_period_in_wl_value', 'vendor_retrain_period_in_wl_value');
			$table->renameColumn('vendor_retain_period_in_wl_unit', 'vendor_retrain_period_in_wl_unit');

			$table->decimal('minimum_vpe_score_to_exit_wl_value', 19, 2)->default(70);
			$table->integer('minimum_vpe_score_to_exit_wl_unit')->default(1);
		});
	}

}
