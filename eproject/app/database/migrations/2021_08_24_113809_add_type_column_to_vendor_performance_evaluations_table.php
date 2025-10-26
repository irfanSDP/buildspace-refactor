<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use PCK\VendorPerformanceEvaluation\VendorPerformanceEvaluation;

class AddTypeColumnToVendorPerformanceEvaluationsTable extends Migration
{
	public function up()
	{
		Schema::table('vendor_performance_evaluations', function(Blueprint $table)
        {
			$table->integer('type')->default(VendorPerformanceEvaluation::TYPE_360);
		});
	}

	public function down()
	{
		Schema::table('vendor_performance_evaluations', function (Blueprint $table)
        {
            $table->dropColumn('type');
        });
	}
}
