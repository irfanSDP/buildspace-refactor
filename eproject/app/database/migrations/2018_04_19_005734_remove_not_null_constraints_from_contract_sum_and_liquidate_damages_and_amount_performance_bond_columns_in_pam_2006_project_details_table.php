<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveNotNullConstraintsFromContractSumAndLiquidateDamagesAndAmountPerformanceBondColumnsInPam2006ProjectDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::statement('ALTER TABLE pam_2006_project_details ALTER COLUMN contract_sum DROP NOT NULL');
        DB::statement('ALTER TABLE pam_2006_project_details ALTER COLUMN liquidate_damages DROP NOT NULL');
        DB::statement('ALTER TABLE pam_2006_project_details ALTER COLUMN amount_performance_bond DROP NOT NULL');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        \PCK\ProjectDetails\PAM2006ProjectDetail::whereNull('contract_sum')->update(array( 'contract_sum' => 0 ));
        \PCK\ProjectDetails\PAM2006ProjectDetail::whereNull('liquidate_damages')->update(array( 'liquidate_damages' => 0 ));
        \PCK\ProjectDetails\PAM2006ProjectDetail::whereNull('amount_performance_bond')->update(array( 'amount_performance_bond' => 0 ));

        DB::statement('ALTER TABLE pam_2006_project_details ALTER COLUMN contract_sum SET NOT NULL');
        DB::statement('ALTER TABLE pam_2006_project_details ALTER COLUMN liquidate_damages SET NOT NULL');
        DB::statement('ALTER TABLE pam_2006_project_details ALTER COLUMN amount_performance_bond SET NOT NULL');
	}

}
