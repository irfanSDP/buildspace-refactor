<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use PCK\Users\User;
use PCK\VendorPerformanceEvaluation\Cycle;

class AddVendorManagementGradeIdColumnToVendorPerformanceEvaluationCyclesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('vendor_performance_evaluation_cycles', function(Blueprint $table)
		{
			$table->unsignedInteger('vendor_management_grade_id')->nullable();

			$table->foreign('vendor_management_grade_id')->references('id')->on('vendor_management_grades')->onDelete('set null');
		});

        $this->generateVendorManagementGradesForHistoricalCycles();
	}

    private function generateVendorManagementGradesForHistoricalCycles()
    {
        $superAdminIds = User::getSuperAdminIds();

        sort($superAdminIds);

        \Auth::loginUsingId($superAdminIds[0]);

        $completedCycles = Cycle::where('is_completed', true)->whereNull('vendor_management_grade_id')->orderBy('id', 'ASC')->get();

        foreach($completedCycles as $cycle)
        {
            $clonedGrade = Cycle::getClonedVendorManagementGrade();

            $query = DB::raw('UPDATE ' . (new Cycle)->getTable() . ' SET vendor_management_grade_id = ' . $clonedGrade->id . ' WHERE id = ' . $cycle->id . ';');

            DB::update($query);
        }
    }


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('vendor_performance_evaluation_cycles', function(Blueprint $table)
		{
			$table->dropColumn('vendor_management_grade_id');
		});
	}

}
